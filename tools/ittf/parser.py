"""BeautifulSoup parsers for ITTF portal HTML pages."""

import re
from typing import Any

from bs4 import BeautifulSoup


def parse_ranking_table(html: str, gender: str = "men") -> list[dict[str, Any]]:
    """Parse ITTF ranking table HTML into structured data.

    Args:
        html: Raw HTML of the rankings page.
        gender: 'men' or 'women'.

    Returns:
        List of dicts with keys: ittf_id, position, points, name,
        country, continent, gender.
    """
    soup = BeautifulSoup(html, "html.parser")
    rows = soup.select("table[id^='list_'] tr.fabrik_row, table[id^='list_'] tr.fabrik_row___")

    results = []
    for row in rows:
        entry = _parse_ranking_row(row, gender)
        if entry:
            results.append(entry)

    return results


def _parse_ranking_row(row, gender: str) -> dict[str, Any] | None:
    """Parse a single ranking table row."""
    cells = row.find_all("td")
    if not cells:
        return None

    entry: dict[str, Any] = {"gender": gender}

    for cell in cells:
        class_list = cell.get("class", [])
        cls = " ".join(class_list) if class_list else ""

        if "PID_raw" in cls:
            entry["ittf_id"] = cell.get_text(strip=True)
        elif "Position" in cls and "PID" not in cls:
            entry["position"] = _parse_int(cell.get_text(strip=True))
        elif "Points" in cls:
            entry["points"] = _parse_int(cell.get_text(strip=True))
        elif "Name_raw" in cls or ("Name" in cls and "raw" not in cls):
            name_link = cell.find("a")
            if name_link:
                entry["name"] = name_link.get_text(strip=True)
            else:
                entry["name"] = cell.get_text(strip=True)
        elif "Country_raw" in cls:
            entry["country"] = cell.get_text(strip=True)
        elif "ITTF_raw" in cls:
            entry["continent"] = cell.get_text(strip=True)
        elif "Num" in cls:
            entry["num"] = _parse_int(cell.get_text(strip=True))

    # Ensure essential fields are present
    if "ittf_id" not in entry or not entry["ittf_id"]:
        # Try to extract from the name link URL
        for cell in row.find_all("td"):
            link = cell.find("a")
            if link and "player_id_raw" in link.get("href", ""):
                match = re.search(r"player_id_raw[^=]*=(\d+)", link["href"])
                if match:
                    entry["ittf_id"] = match.group(1)
                    break

    if "name" not in entry:
        return None

    return entry


def parse_player_profile(html: str) -> dict[str, Any]:
    """Parse player profile page HTML.

    Args:
        html: Raw HTML of the player profile page.

    Returns:
        Dict with player info and recent matches.
    """
    soup = BeautifulSoup(html, "html.parser")
    result: dict[str, Any] = {
        "profile": {},
        "recent_matches": [],
    }

    # Player info from the profile table
    profile_table = soup.select_one("table[id^='list_60']")
    if profile_table:
        row = profile_table.select_one("tr.fabrik_row, tr.fabrik_row___")
        if row:
            for cell in row.find_all("td"):
                cls = " ".join(cell.get("class", []))
                if "player_id_raw" in cls:
                    result["profile"]["ittf_id"] = cell.get_text(strip=True)
                elif "name_raw" in cls:
                    result["profile"]["name"] = cell.get_text(strip=True)
                elif "profile_raw" in cls or "profile" in cls:
                    result["profile"]["details"] = cell.get_text(
                        separator=" ", strip=True
                    )
                elif "career_stats_raw" in cls:
                    result["profile"]["career_stats"] = cell.get_text(
                        separator=" ", strip=True
                    )
                elif "ytd_stats_raw" in cls:
                    result["profile"]["ytd_stats"] = cell.get_text(
                        separator=" ", strip=True
                    )

    # Recent matches from match-item divs
    for match_div in soup.select("div.match-item"):
        match_data = _parse_match_item(match_div)
        if match_data:
            result["recent_matches"].append(match_data)

    return result


def _parse_match_item(match_div) -> dict[str, Any]:
    """Parse a single match-item div."""
    texts = match_div.get_text(separator="\n", strip=True).split("\n")
    lines = [t.strip() for t in texts if t.strip()]

    match_data: dict[str, Any] = {}

    for line in lines:
        lower = line.lower()

        if " vs " in line:
            players = re.split(r"\s+vs\s+", line, maxsplit=1)
            if len(players) == 2:
                match_data["player_a"] = players[0].strip()
                match_data["player_b"] = players[1].strip()

        elif lower.startswith("ms ") or lower.startswith("ws ") or \
                lower.startswith("jbs ") or lower.startswith("cbs ") or \
                lower.startswith("u21ms") or lower.startswith("xd"):
            match_data["event_type"] = line.split()[0]
            rest = " ".join(line.split()[1:])
            if " - " in rest:
                parts = rest.split(" - ", 1)
                match_data["stage"] = parts[0].strip()
                if "|" in parts[1]:
                    round_score = parts[1].split("|", 1)
                    match_data["round"] = round_score[0].strip()
                    match_data["score"] = round_score[1].strip()
                else:
                    match_data["score"] = parts[1].strip()

        elif "result:" in lower:
            match_data["result"] = line.split(":", 1)[1].strip()

        elif "tournament:" in lower or "event:" in lower:
            match_data["tournament"] = line.split(":", 1)[1].strip()

    return match_data


def parse_pagination_info(html: str) -> dict[str, Any]:
    """Extract pagination info from a rankings page.

    Returns:
        Dict with 'total' (int) and 'current_page' (int).
    """
    soup = BeautifulSoup(html, "html.parser")

    # Look for pagination text like "Page 1 of 50" or similar
    pages_text = soup.select_one(".fabrikPageNav .pages-text, .pagination .pages-counter")
    if pages_text:
        match = re.search(r"of\s+(\d+)", pages_text.get_text())
        if match:
            return {"total_pages": int(match.group(1))}

    # Count limitstart values in pagination links
    links = soup.select("a[href*='limitstart']")
    starts = set()
    for link in links:
        m = re.search(r"limitstart\d*=(\d+)", link.get("href", ""))
        if m:
            starts.add(int(m.group(1)))

    if starts:
        max_start = max(starts)
        return {"total_pages": (max_start // 25) + 1}

    # Fallback: count total rows from the table
    table = soup.select_one("table[id^='list_']")
    if table:
        rows = table.select("tr.fabrik_row, tr.fabrik_row___")
        return {"total_rows": len(rows)}

    return {}


def _parse_int(value: str) -> int:
    """Parse integer, returning 0 on failure."""
    try:
        return int(value.replace(",", "").strip())
    except (ValueError, AttributeError):
        return 0
