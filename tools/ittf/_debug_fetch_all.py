import sys, json, time
from pathlib import Path
sys.path.insert(0, str(Path(__file__).parent))

from auth import IttfSession
from config import RANKING_URLS, ROWS_PER_PAGE, IMPORT_DIR
from parser import parse_ranking_table

session = IttfSession()
if not session.load():
    print("No session!")
    sys.exit(1)

# Fetch ALL remaining women's ranking pages (pages 3-19, offset 100-900)
# and all men's pages (pages 11+, offset 500+)

# First, collect the remaining women's data
print("Fetching all women's ranking pages (3-19)...")
all_women = []
for page in range(3, 20):
    offset = page * ROWS_PER_PAGE
    url = f"{RANKING_URLS['women']}?limitstart58={offset}"
    print(f"  Page {page+1} (offset {offset})... ", end="", flush=True)
    try:
        resp = session.get(url, timeout=30)
        rows = parse_ranking_table(resp.text, gender="women")
    except Exception as e:
        print(f"ERROR: {e}")
        break
    if not rows:
        print("No more rows.")
        break
    all_women.extend(rows)
    pos_range = f"{rows[0].get('position','?')}-{rows[-1].get('position','?')}" if rows else "?"
    print(f"{len(rows)} players (pos {pos_range})")
    time.sleep(0.3)

# Save all women's ranking data
output = {
    "source": "ITTF women rankings (all pages)",
    "fetched_at": "2026-07-06",
    "gender": "women",
    "count": len(all_women),
    "rows": [{"ittf_id": r.get("ittf_id",""), "name": r.get("name",""), "position": r.get("position",0), "country": r.get("country",""), "points": r.get("points",0)} for r in all_women],
}
filepath = IMPORT_DIR / "rankings_women_2026-07-06_all.json"
filepath.write_text(json.dumps(output, indent=2, ensure_ascii=False))
print(f"\nSaved {len(all_women)} women's rankings")

# Check which missing players we found
missing_women = ["TSAI", "COSSIO", "MISCHEK", "VARADY", "ZARIF"]
for row in all_women:
    name_upper = row.get("name","").upper()
    for m in missing_women:
        if m in name_upper:
            print(f"  FOUND: {row.get('name','')} (ID: {row.get('ittf_id','')}, Rank: {row.get('position','?')})")
            missing_women.remove(m)
            break

if missing_women:
    print(f"Still missing in all women's data: {missing_women}")
