import sys, json, re, time
sys.path.insert(0, r"C:\xampp\htdocs\TT_match\tools\ittf")

from auth import IttfSession
from config import PLAYER_PROFILE_URL

creds = json.loads(open(r"C:\xampp\htdocs\TT_match\tools\ittf\credentials.json").read())
session = IttfSession()
if not session.load():
    session.login(creds['username'], creds['password'])

# Load DB players
with open(r"C:\xampp\htdocs\TT_match\temp_players_utf8.json", "r", encoding="utf-8") as f:
    db_players = json.load(f)

print(f"Loaded {len(db_players)} players from DB")

def normalize_name(name):
    """Normalize a name for comparison. Extracts surname and given parts."""
    # ITTF portal format: "SURNAME Given" - surname is ALL CAPS
    # DB format varies: "Given SURNAME", "Given SURNAME" in first_name, etc.
    parts = name.strip().split()
    surname_words = []
    given_words = []
    for part in parts:
        # Check if this is an ALL-CAPS word (surname in ITTF format)
        # or a mixed-case word (given name)
        clean = re.sub(r'[^A-Z]', '', part)  # letters only
        if part == part.upper() and len(clean) > 1 and not re.search(r'\d', part):
            surname_words.append(part.upper())
        else:
            given_words.append(part)
    return ' '.join(surname_words).upper(), ' '.join(given_words).lower()

def fetch_portal_name(ittf_id):
    url = f'{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}'
    try:
        resp = session.get(url, timeout=30)
    except Exception as e:
        return None, str(e)
    
    html = resp.text
    # Pattern 1: HTML cell
    match = re.search(r'vw_profiles___name[^>]*>\s*([A-Z][A-Za-z\s\-\']+?)\s*\(#\d+\)\s*</td>', html)
    if match:
        return match.group(1).strip(), None
    # Pattern 2: JSON data
    match = re.search(r'"vw_profiles___player_id":"([^(]+?)"', html)
    if match:
        return match.group(1).strip(), None
    return None, "Name not found"

true_mismatches = []
name_only_differences = []
ok_matches = []
errors = []

for i, player in enumerate(db_players):
    ittf_id = player['ittf_id']
    db_full = player['full_name']  # e.g., "Alvaro ROBLES" or "Chuqin WANG"
    
    portal_name, error = fetch_portal_name(ittf_id)
    
    if error:
        errors.append({'ittf_id': ittf_id, 'db_name': db_full, 'error': error})
        print(f"[{i+1}/{len(db_players)}] {ittf_id} | ERROR: {error}")
        continue
    
    # Normalize both names for comparison
    db_surname, db_given = normalize_name(db_full)
    portal_surname, portal_given = normalize_name(portal_name)
    
    surname_match = (db_surname == portal_surname)
    given_match = (db_given == portal_given)
    
    if surname_match and given_match:
        ok_matches.append({'ittf_id': ittf_id, 'db_name': db_full, 'portal_name': portal_name})
        print(f"[{i+1}/{len(db_players)}] {ittf_id} | OK: {db_full} = {portal_name}")
    else:
        # Check if it's just a name order difference (same names, different format)
        # or a completely different player
        # Split DB name into individual words
        db_words = set(db_full.lower().split())
        portal_words = set(portal_name.lower().split())
        
        # If the word sets overlap significantly, it's likely same player, name format issue
        common = db_words & portal_words
        if len(common) >= 2 and (len(common) / max(len(db_words), len(portal_words))) >= 0.5:
            name_only_differences.append({
                'ittf_id': ittf_id, 'db_name': db_full, 'portal_name': portal_name,
                'country_db': player['country_code']
            })
            print(f"[{i+1}/{len(db_players)}] {ittf_id} | NAME_ONLY: {db_full} vs {portal_name}")
        else:
            true_mismatches.append({
                'ittf_id': ittf_id, 'db_name': db_full, 'portal_name': portal_name,
                'country_db': player['country_code']
            })
            print(f"[{i+1}/{len(db_players)}] {ittf_id} | REAL MISMATCH: DB={db_full} vs Portal={portal_name}")
    
    if i < len(db_players) - 1:
        time.sleep(1.0)
    
    if (i + 1) % 30 == 0:
        print(f"\n--- Progress: {i+1}/{len(db_players)} ---")

print("\n" + "=" * 100)
print(f"FINAL RESULTS:")
print(f"  Total players:          {len(db_players)}")
print(f"  Correct (OK):           {len(ok_matches)}")
print(f"  Name format only:       {len(name_only_differences)}")
print(f"  TRUE ID MISMATCHES:     {len(true_mismatches)}")
print(f"  Errors:                 {len(errors)}")

print(f"\nTRUE ID MISMATCHES ({len(true_mismatches)}):")
print("-" * 100)
for m in true_mismatches:
    print(f"  ITTF ID {m['ittf_id']}: DB={m['db_name']:30s} ({m['country_db']}) -> Portal={m['portal_name']}")

print(f"\nNAME FORMAT ONLY ({len(name_only_differences)}):")
print("-" * 100)
for m in name_only_differences:
    print(f"  ITTF ID {m['ittf_id']}: DB={m['db_name']:30s} <-> Portal={m['portal_name']}")

# Save
results = {
    'total': len(db_players),
    'ok': len(ok_matches),
    'name_format_only': len(name_only_differences),
    'true_mismatches': len(true_mismatches),
    'errors': len(errors),
    'mismatch_details': true_mismatches,
    'name_format_details': name_only_differences,
}
with open(r"C:\xampp\htdocs\TT_match\temp_ittf_id_final_analysis.json", 'w') as f:
    json.dump(results, f, indent=2)

