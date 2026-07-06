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
                    details = cell.get_text(separator=" ", strip=True)
                    result["profile"]["details"] = details
                    # Extract birth_year from details like "JAPAN Gender: Female Birth Year: 2000 Age: 25 ..."
                    # HTML format may include spans: "Birth Year: <span class='notranslate'>2000</span>"
                    import re
                    birth_match = re.search(r"Birth Year:\s*(?:<[^>]+>)?\s*(\d{4})", details)
                    if birth_match:
                        result["profile"]["birth_year"] = int(birth_match.group(1))
                    age_match = re.search(r"Age:\s*(\d+)", details)
                    if age_match:
                        result["profile"]["age"] = int(age_match.group(1))
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
    """Parse a single match-item div using structured span elements.

    Two possible structures:

    **Type A (detailed match, 9 spans):**
        1. Tournament name (year embedded, e.g. 'Macao 2026')
        2. Player A name with country
        3. Player B name with country
        4. Event type (MS, WS, XD, MD, WD, etc.)
        5. Stage (Main Draw, Qualification)
        6. Round (Final, SemiFinal, QuarterFinal, R16, R32, R64)
        7. Score (e.g. '4 - 3')
        8. Detailed set scores (e.g. '9:11 18:16 11:8...')
        9. Result (WON or LOST)

    **Type B (player-event summary, 6 spans):**
        0. Year
        1. Tournament name
        2. Event type
        3. Stage
        4. Round
        5. Winner name with country
    """
    spans = match_div.find_all("span")
    match_data: dict[str, Any] = {}
    num_spans = len(spans)

    if num_spans == 6:
        # Type B — player-event summary
        match_data["year"] = spans[0].get_text(strip=True)
        match_data["tournament"] = spans[1].get_text(strip=True)

        if num_spans >= 3:
            match_data["event_type"] = spans[2].get_text(strip=True)
        if num_spans >= 4:
            match_data["stage"] = spans[3].get_text(strip=True)
        if num_spans >= 5:
            match_data["round"] = spans[4].get_text(strip=True)
        if num_spans >= 6:
            match_data["winner"] = spans[5].get_text(strip=True)
            match_data["result"] = "WON"

    elif num_spans >= 3:
        # Type A — detailed match
        tournament_raw = spans[0].get_text(strip=True)
        match_data["tournament"] = tournament_raw

        year_match = re.search(r'\b(20\d{2})\b', tournament_raw)
        if year_match:
            match_data["year"] = year_match.group(1)

        match_data["player_a"] = spans[1].get_text(strip=True)
        match_data["player_b"] = spans[2].get_text(strip=True)

        if num_spans >= 4:
            match_data["event_type"] = spans[3].get_text(strip=True)
        if num_spans >= 5:
            match_data["stage"] = spans[4].get_text(strip=True)
        if num_spans >= 6:
            match_data["round"] = spans[5].get_text(strip=True)
        if num_spans >= 7:
            match_data["score"] = spans[6].get_text(strip=True)
        if num_spans >= 8:
            match_data["detailed_sets"] = spans[7].get_text(strip=True)
        if num_spans >= 9:
            result_text = spans[8].get_text(strip=True)
            match_data["result"] = result_text

            def split_player(text: str) -> tuple[str, str]:
                m = re.match(r'^(.+?)\s*\(([^)]+)\)\s*$', text)
                if m:
                    return m.group(1).strip(), m.group(2).strip()
                return text, ""

            player_a_name, _ = split_player(match_data.get("player_a", ""))
            if result_text.upper() == "WON":
                match_data["winner"] = player_a_name
            elif result_text.upper() == "LOST":
                _, player_b_name = split_player(match_data.get("player_b", ""))
                match_data["winner"] = match_data.get("player_b", "")

    return match_data


def parse_player_matches(html: str) -> list[dict[str, Any]]:
    """Parse all match items from a player profile page (div.match-item format).

    Args:
        html: Raw HTML of the player profile page.

    Returns:
        List of match dicts with keys: tournament, year, player_a,
        player_b, event_type, stage, round, score, detailed_sets, result, winner.
    """
    soup = BeautifulSoup(html, "html.parser")
    results = []
    for match_div in soup.select("div.match-item"):
        match_data = _parse_match_item(match_div)
        if match_data and match_data.get("tournament"):
            results.append(match_data)
    return results


def parse_player_matches_table(html: str) -> list[dict[str, Any]]:
    """Parse all match rows from the player-matches Fabrik table page.

    The table has 14 cells per row:
        [0] Year | [1] Tournament | [2] Player A (with country) | [3] Partner A
        [4] Player B (with country) | [5] Partner B | [6] Event | [7] Stage
        [8] Round | [9] Score | [10] Sets | [11] Winner | [12] Runner-up | [13] -

    Only extracts singles matches (no partner in cells 3 and 5).

    Returns:
        Same format as parse_player_matches().
    """
    soup = BeautifulSoup(html, "html.parser")
    results = []

    for row in soup.select("table[id^='list_'] tr.fabrik_row, table[id^='list_'] tr.fabrik_row___"):
        cells = row.find_all("td")
        if len(cells) < 11:
            continue

        partner_a = cells[3].get_text(strip=True) if len(cells) > 3 else ""
        partner_b = cells[5].get_text(strip=True) if len(cells) > 5 else ""

        # Skip doubles / team matches (partner columns are non-empty)
        if partner_a or partner_b:
            continue

        year = cells[0].get_text(strip=True)
        tournament = cells[1].get_text(strip=True)
        player_a = cells[2].get_text(strip=True)
        player_b = cells[4].get_text(strip=True)
        event_type = cells[6].get_text(strip=True) if len(cells) > 6 else ""
        stage = cells[7].get_text(strip=True) if len(cells) > 7 else ""
        round_str = cells[8].get_text(strip=True) if len(cells) > 8 else ""
        score = cells[9].get_text(strip=True) if len(cells) > 9 else ""
        detailed_sets = cells[10].get_text(strip=True) if len(cells) > 10 else ""

        winner = cells[11].get_text(strip=True) if len(cells) > 11 else ""

        if not tournament or not player_a or not player_b:
            continue

        # Determine match result (WON/LOST) by checking if player_a is the winner
        def _extract_name(text: str) -> str:
            m = re.match(r'^(.+?)\s*\(', text)
            return m.group(1).strip() if m else text.strip()

        a_name = _extract_name(player_a)
        b_name = _extract_name(player_b)

        result = ""
        if winner:
            w_name = _extract_name(winner)
            if w_name.lower() == a_name.lower():
                result = "WON"
            elif w_name.lower() == b_name.lower():
                result = "LOST"

        results.append({
            "year": year,
            "tournament": tournament,
            "player_a": player_a,
            "player_b": player_b,
            "event_type": event_type,
            "stage": stage,
            "round": round_str,
            "score": score,
            "detailed_sets": detailed_sets,
            "result": result,
            "winner": winner,
        })

    return results


def parse_pagination_info(html: str) -> dict[str, Any]:
    """Extract pagination info from a Fabrik list page (rankings, matches, etc.).

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
