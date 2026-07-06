import sys
sys.path.insert(0, r"C:\xampp\htdocs\TT_match\tools\ittf")
import json

from auth import IttfSession
from config import PLAYER_PROFILE_URL

creds = json.loads(open(r"C:\xampp\htdocs\TT_match\tools\ittf\credentials.json").read())
session = IttfSession()
if not session.load():
    session.login(creds['username'], creds['password'])

# Fetch one profile and dump the HTML around the name area
ittf_id = '113679'
url = f'{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}'
resp = session.get(url, timeout=30)

# Save full HTML for inspection
with open(r"C:\xampp\htdocs\TT_match\temp_profile_113679.html", "w", encoding="utf-8") as f:
    f.write(resp.text)

print(f"Saved HTML ({len(resp.text)} bytes)")

# Search for any name-like patterns
import re
# Look for the actual data rows
matches = re.findall(r'vw_profiles___name.*?(?:<[^>]*>)*\s*([A-Z][a-zA-ZÀ-ÿ\s\-]+)', resp.text[:10000])
print(f"Name matches: {matches[:10]}")

# Also search for the player ID
id_matches = re.findall(r'113679.*?(?:<[^>]*>)*\s*([A-Z][a-zA-ZÀ-ÿ\s\-]+)', resp.text[:10000])
print(f"ID 113679 context matches: {id_matches[:10]}")
