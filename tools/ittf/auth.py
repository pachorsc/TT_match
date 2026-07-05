"""ITTF results portal authentication via requests.Session()."""

import json
import re
import time
from pathlib import Path

import requests
from bs4 import BeautifulSoup

from config import BASE_URL, LOGIN_URL, SESSION_PATH


class IttfSession:
    """Manages authenticated session with the ITTF results portal."""

    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            "User-Agent": (
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                "AppleWebKit/537.36 (KHTML, like Gecko) "
                "Chrome/120.0.0.0 Safari/537.36"
            ),
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language": "en-US,en;q=0.5",
        })
        self._logged_in = False

    def _extract_csrf_token(self, html: str) -> tuple[str | None, str | None]:
        """Extract CSRF token and return URL from login form."""
        soup = BeautifulSoup(html, "html.parser")
        token_input = soup.find("input", {"name": "return"})
        return_url = token_input.get("value") if token_input else None

        # Try multiple CSRF token locations
        for meta in soup.find_all("meta", {"name": "csrf-token"}):
            token = meta.get("content")
            if token:
                return token, return_url

        for input_tag in soup.find_all("input", {"name": re.compile(r"csrf|token", re.I)}):
            token = input_tag.get("value")
            if token:
                return f"{input_tag.get('name')}={token}", return_url

        # Joomla sometimes uses a hidden input with a specific id
        for input_tag in soup.find_all("input", {"type": "hidden"}):
            name = input_tag.get("name", "")
            if "token" in name.lower() or "csrf" in name.lower():
                return f"{name}={input_tag.get('value')}", return_url

        return None, return_url

    def login(self, username: str, password: str) -> bool:
        """Login to the ITTF results portal.

        Args:
            username: ITTF portal username.
            password: ITTF portal password.

        Returns:
            True if login succeeded, False otherwise.
        """
        # Step 1: GET login page to obtain CSRF token
        resp = self.session.get(LOGIN_URL, timeout=30)
        resp.raise_for_status()

        csrf_param, return_url = self._extract_csrf_token(resp.text)
        if not return_url:
            return_url = "index.php"

        # Step 2: POST credentials
        form_data = {
            "username": username,
            "password": password,
            "return": return_url,
            "option": "com_users",
            "task": "user.login",
        }

        if csrf_param and "=" in csrf_param:
            key, value = csrf_param.split("=", 1)
            form_data[key] = value

        post_resp = self.session.post(
            LOGIN_URL,
            data=form_data,
            headers={"Referer": LOGIN_URL},
            timeout=30,
        )

        # Step 3: Verify login
        self._logged_in = "Hi " in post_resp.text and "Juan" in post_resp.text

        if self._logged_in:
            self.save()

        return self._logged_in

    @property
    def is_logged_in(self) -> bool:
        return self._logged_in

    def get(self, url: str, **kwargs) -> requests.Response:
        """Make authenticated GET request."""
        if not self._logged_in:
            raise RuntimeError("Not logged in. Call login() first.")
        return self.session.get(url, timeout=kwargs.pop("timeout", 60), **kwargs)

    def save(self) -> None:
        """Save session cookies to disk."""
        SESSION_PATH.parent.mkdir(parents=True, exist_ok=True)
        cookies = self.session.cookies.get_dict()
        data = {
            "cookies": cookies,
            "saved_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        }
        SESSION_PATH.write_text(json.dumps(data, indent=2))

    def load(self) -> bool:
        """Load saved session cookies from disk.

        Returns:
            True if session was loaded, False otherwise.
        """
        if not SESSION_PATH.exists():
            return False

        data = json.loads(SESSION_PATH.read_text())
        cookies = data.get("cookies", {})
        for name, value in cookies.items():
            self.session.cookies.set(name, value)

        # Verify session is still valid
        try:
            resp = self.session.get(BASE_URL, timeout=30)
            if "Hi " in resp.text and "Juan" in resp.text:
                self._logged_in = True
                return True
            # Try login page - if already logged in, it redirects
            if "logout" in resp.text.lower():
                self._logged_in = True
                return True
        except requests.RequestException:
            pass

        return False
