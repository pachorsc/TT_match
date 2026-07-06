#!/usr/bin/env python3
"""Quick test: login and fetch one player profile."""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from auth import IttfSession
from config import PLAYER_PROFILE_URL
import json
import re

# Load credentials
creds_path = Path(__file__).resolve().parent / "credentials.json"
creds = json.loads(creds_path.read_text())

session = IttfSession()

# Try loading saved session first
if session.load():
    print("Loaded saved session")
else:
    print("Logging in...")
    if session.login(creds["username"], creds["password"]):
        print("Login OK")
    else:
        print("Login FAILED")
        sys.exit(1)

# Fetch player profile
ittf_id = "113679"  # Alvaro Robles
url = f"{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}"

print(f"\nFetching profile for ITTF ID {ittf_id}...")
resp = session.get(url, timeout=30)
print(f"Status: {resp.status_code}")
print(f"Body length: {len(resp.text)}")

# Search for birth year
html = resp.text
match = re.search(r"Birth Year:\s*(\d{4})", html)
if match:
    print(f"Birth Year: {match.group(1)}")
else:
    print("Birth Year NOT found")
    # Show context around "fabrik_row"
    if "fabrik_row" in html:
        pos = html.find("fabrik_row")
        print(f"\nAround fabrik_row:\n{html[pos:pos+1000]}")
