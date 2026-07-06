import sys
sys.path.insert(0, r"C:\xampp\htdocs\TT_match\tools\ittf")
import json, re

from auth import IttfSession
from config import PLAYER_PROFILE_URL

creds = json.loads(open(r"C:\xampp\htdocs\TT_match\tools\ittf\credentials.json").read())
session = IttfSession()
if not session.load():
    session.login(creds['username'], creds['password'])

# Test 10 specific IDs
test_ids = ['113679', '121558', '131163', '123980', '111066', '115641', '114715', '135977', '132992', '107028']
for ittf_id in test_ids:
    url = f'{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}'
    resp = session.get(url, timeout=30)
    name_match = re.search(r"vw_profiles___name[^>]*>\s*(?:<[^>]+>)*\s*([A-Z].+?)\s*(?:<|#)", resp.text)
    name = name_match.group(1).strip() if name_match else 'Unknown'
    print(f'{ittf_id} -> {name}')
