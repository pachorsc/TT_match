import sys
sys.path.insert(0, r"C:\xampp\htdocs\TT_match\tools\ittf")
import json, re, time

from auth import IttfSession
from config import PLAYER_PROFILE_URL

creds = json.loads(open(r"C:\xampp\htdocs\TT_match\tools\ittf\credentials.json").read())
session = IttfSession()
if not session.load():
    session.login(creds['username'], creds['password'])

# DB players from the PHP script output
db_players = {}
import subprocess
result = subprocess.run(
    ['php', 'artisan', 'tinker', '--execute',
     'echo json_encode(App\\Models\\Player::whereNotNull(\'ittf_id\')->where(\'ittf_id\',\'!=\',\'\')'
     '->get([\'ittf_id\',\'first_name\',\'last_name\',\'country_code\',\'date_of_birth\'])->toArray());'],
    capture_output=True, text=True, cwd=r"C:\xampp\htdocs\TT_match", timeout=30
)
try:
    players_data = json.loads(result.stdout.strip())
    for p in players_data:
        ittf_id = str(p['ittf_id'])
        db_name = f"{p['first_name']} {p['last_name']}".strip()
        db_players[ittf_id] = {
            'db_name': db_name,
            'first_name': p['first_name'],
            'last_name': p['last_name'],
            'country': p.get('country_code', ''),
            'dob': p.get('date_of_birth', None)
        }
    print(f"Loaded {len(db_players)} players from DB")
except Exception as e:
    print(f"Error loading DB data: {e}")
    # Fallback: parse from PHP output
    sys.exit(1)

def fetch_portal_name(ittf_id):
    """Fetch the actual player name from ITTF portal for a given ID."""
    url = f'{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}'
    try:
        resp = session.get(url, timeout=30)
    except Exception as e:
        return None, f"HTTP error: {e}"
    
    html = resp.text
    
    # Pattern 1: Look for the player name cell "SURNAME Given (#ID)"
    match = re.search(r'vw_profiles___name[^>]*>\s*([A-Z][A-Za-z\s\-\']+?)\s*\(#\d+\)\s*</td>', html)
    if match:
        return match.group(1).strip(), None
    
    # Pattern 2: Extract from JSON data embedded in page
    match = re.search(r'"vw_profiles___player_id":"([^"]+?)"', html)
    if match:
        return match.group(1).strip(), None
    
    # Pattern 3: Extract from the text content near the player ID
    match = re.search(r'>([A-Z][A-Za-z\s\-\']+?)\s*\(#\d+\)<', html)
    if match:
        return match.group(1).strip(), None
    
    return None, "Name not found in HTML"

# Check ALL players
print(f"\nChecking {len(db_players)} ITTF IDs against portal...\n")
print("=" * 100)

mismatches = []
matches = []
errors = []

for i, (ittf_id, info) in enumerate(sorted(db_players.items())):
    portal_name, error = fetch_portal_name(ittf_id)
    
    if error:
        errors.append({'ittf_id': ittf_id, 'db_name': info['db_name'], 'error': error})
        print(f"[{i+1}/{len(db_players)}] {ittf_id} | DB: {info['db_name']:30s} | ERROR: {error}")
        continue
    
    # Normalize names for comparison
    # ITTF portal format: "SURNAME Given" (e.g., "BOULOUSSA Mehdi")
    # DB format: "Given SURNAME" (e.g., "Alvaro ROBLES")
    
    # Extract surname from portal name (all-caps part)
    portal_parts = portal_name.split()
    portal_surname_words = []
    portal_given_words = []
    for part in portal_parts:
        if part == part.upper() and len(part) > 1:
            portal_surname_words.append(part)
        else:
            portal_given_words.append(part)
    
    portal_surname = ' '.join(portal_surname_words).upper()
    portal_given = ' '.join(portal_given_words)
    
    # DB name is already in "Given SURNAME" format
    db_surname = info['last_name'].upper().strip() if info['last_name'] else ''
    db_given = info['first_name'].strip() if info['first_name'] else ''
    
    # Compare
    surname_match = (portal_surname == db_surname)
    given_match = (portal_given.lower().strip() == db_given.lower().strip())
    
    status = "OK" if (surname_match and given_match) else "MISMATCH"
    
    if status == "MISMATCH":
        mismatches.append({
            'ittf_id': ittf_id,
            'db_name': info['db_name'],
            'db_surname': db_surname,
            'db_given': db_given,
            'portal_name': portal_name,
            'portal_surname': portal_surname,
            'portal_given': portal_given,
            'country': info['country']
        })
        print(f"[{i+1}/{len(db_players)}] {ittf_id} | MISMATCH!")
        print(f"         DB:     {info['db_name']:30s} ({info['country']})")
        print(f"         Portal: {portal_name:30s}")
    else:
        matches.append({'ittf_id': ittf_id, 'db_name': info['db_name'], 'portal_name': portal_name})
        # Print every match too for completeness (just compact)
        print(f"[{i+1}/{len(db_players)}] {ittf_id} | OK: {info['db_name']:30s} = {portal_name}")
    
    # Rate limiting
    if i < len(db_players) - 1:
        time.sleep(1.0)
    
    # Save intermediate results every 30 players
    if (i + 1) % 30 == 0:
        print(f"\n--- Progress: {i+1}/{len(db_players)} checked, {len(mismatches)} mismatches, {len(errors)} errors ---\n")

print("\n" + "=" * 100)
print(f"\nRESULTS SUMMARY:")
print(f"  Total players checked: {len(db_players)}")
print(f"  Correct matches:       {len(matches)}")
print(f"  MISMATCHES:            {len(mismatches)}")
print(f"  Errors:                {len(errors)}")

if mismatches:
    print(f"\nDETAILED MISMATCHES ({len(mismatches)}):")
    print("-" * 100)
    for m in mismatches:
        print(f"  ITTF ID {m['ittf_id']}:")
        print(f"    DB says:     {m['db_name']} ({m['country']})")
        print(f"    Portal says: {m['portal_name']}")
        print()

# Save results to JSON
results = {
    'total': len(db_players),
    'matches': len(matches),
    'mismatches': len(mismatches),
    'errors': len(errors),
    'mismatch_details': mismatches,
    'error_details': errors
}
with open(r"C:\xampp\htdocs\TT_match\temp_ittf_id_check_results.json", 'w') as f:
    json.dump(results, f, indent=2)
print(f"\nDetailed results saved to temp_ittf_id_check_results.json")
