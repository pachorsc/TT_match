"""Clerk authentication via Playwright browser automation."""

import json
import time
from pathlib import Path

from config import CLERK_PUBLISHABLE_KEY, STATSTT_URL, TOKEN_PATH


def login_interactive() -> str:
    """Login to StatsTT via browser and capture Clerk JWT token.

    Opens a browser window for the user to login manually.
    Captures the JWT token from network requests.

    Returns:
        JWT token string
    """
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        raise RuntimeError(
            "Playwright not installed. Run:\n"
            "  pip install playwright\n"
            "  playwright install chromium"
        )

    token = None

    def handle_request(request):
        nonlocal token
        auth_header = request.headers.get("authorization", "")
        if auth_header.startswith("Bearer ") and "/api/" in request.url:
            token = auth_header.replace("Bearer ", "")

    def handle_response(response):
        nonlocal token
        auth_header = response.request.headers.get("authorization", "")
        if auth_header.startswith("Bearer ") and "/api/" in response.url:
            token = auth_header.replace("Bearer ", "")

    print("Opening browser for login...")
    print("Please login with your StatsTT account.")
    print("The browser will close automatically after login.\n")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False)
        context = browser.new_context()
        page = context.new_page()

        page.on("request", handle_request)
        page.on("response", handle_response)

        page.goto(STATSTT_URL)

        # Wait for user to login and navigate (max 5 minutes)
        start_time = time.time()
        max_wait = 300  # 5 minutes

        while time.time() - start_time < max_wait:
            time.sleep(2)

            # Check if we captured a token
            if token:
                print("Token captured!")
                break

            # Check if Clerk is loaded and user is signed in
            try:
                is_signed_in = page.evaluate(
                    "() => window.Clerk?.session?.getToken !== undefined"
                )
                if is_signed_in:
                    # Try to get token directly from Clerk
                    token = page.evaluate(
                        "() => window.Clerk?.session?.getToken()"
                    )
                    if token:
                        print("Token captured from Clerk session!")
                        break
            except Exception:
                pass

        browser.close()

    if not token:
        raise RuntimeError(
            "Could not capture token. Make sure you:\n"
            "1. Logged in with your StatsTT account\n"
            "2. Navigated to the SQL Editor page\n"
            "3. Waited for the page to fully load"
        )

    return token


def save_token(token: str) -> None:
    """Save token to disk."""
    data = {
        "token": token,
        "saved_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
    }
    TOKEN_PATH.parent.mkdir(parents=True, exist_ok=True)
    TOKEN_PATH.write_text(json.dumps(data, indent=2))


def load_token() -> str | None:
    """Load saved token from disk."""
    if TOKEN_PATH.exists():
        data = json.loads(TOKEN_PATH.read_text())
        return data.get("token")
    return None


def token_valid(token: str) -> bool:
    """Check if a token is likely valid (basic structure check)."""
    if not token:
        return False
    parts = token.split(".")
    if len(parts) != 3:
        return False
    return True
