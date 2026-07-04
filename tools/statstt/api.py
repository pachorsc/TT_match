"""StatsTT API client."""

import json
import time
from pathlib import Path
from typing import Any

import requests

from config import API_BASE_URL, DAILY_LIMIT, IMPORT_DIR, TOKEN_PATH, QUERIES


class StatsTTAPI:
    """Client for the StatsTT backend API."""

    def __init__(self, token: str | None = None):
        self.base_url = API_BASE_URL
        self.token = token or self._load_token()
        self.session = requests.Session()
        self.session.headers.update({"Content-Type": "application/json"})
        if self.token:
            self.session.headers["Authorization"] = f"Bearer {self.token}"
        self._queries_today = 0

    def _load_token(self) -> str | None:
        """Load saved token from disk."""
        if TOKEN_PATH.exists():
            data = json.loads(TOKEN_PATH.read_text())
            return data.get("token")
        return None

    def save_token(self, token: str, user_info: dict | None = None) -> None:
        """Save token to disk."""
        self.token = token
        self.session.headers["Authorization"] = f"Bearer {token}"
        data = {
            "token": token,
            "saved_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
            "user": user_info or {},
        }
        TOKEN_PATH.write_text(json.dumps(data, indent=2))

    def query(self, sql: str) -> dict[str, Any]:
        """Execute a SQL query against StatsTT.

        Returns:
            dict with keys: columns, rows, row_count, execution_time_ms
        """
        if self._queries_today >= DAILY_LIMIT:
            raise QuotaExceededError(
                f"Daily limit of {DAILY_LIMIT} queries reached. "
                "Try again tomorrow."
            )

        url = f"{self.base_url}/api/query"
        response = self.session.post(url, json={"sql": sql})

        self._queries_today += 1

        if response.status_code == 429:
            body = response.json()
            detail = body.get("detail", {})
            if detail.get("code") == "QUOTA_EXCEEDED":
                raise QuotaExceededError(
                    f"Quota exceeded. Daily limit: {detail.get('daily_limit', DAILY_LIMIT)}"
                )
            raise RateLimitError("Rate limited. Wait 60 seconds.")

        response.raise_for_status()
        return response.json()

    def query_formatted(self, sql: str) -> list[dict[str, Any]]:
        """Execute query and return rows as list of dicts."""
        result = self.query(sql)
        columns = result.get("columns", [])
        rows = result.get("rows", [])
        return [dict(zip(columns, row)) for row in rows]

    def get_schema(self) -> dict[str, Any]:
        """Get database schema (no auth required)."""
        url = f"{self.base_url}/api/schema"
        response = self.session.get(url)
        response.raise_for_status()
        return response.json()

    def get_data_status(self) -> dict[str, Any]:
        """Get data freshness info (no auth required)."""
        url = f"{self.base_url}/api/data-status"
        response = self.session.get(url)
        response.raise_for_status()
        return response.json()

    def get_user_info(self) -> dict[str, Any] | None:
        """Get current user plan and quota info."""
        if not self.token:
            return None
        url = f"{self.base_url}/api/auth/me"
        response = self.session.get(url)
        if response.status_code == 401:
            return None
        response.raise_for_status()
        return response.json()

    @property
    def queries_remaining(self) -> int:
        """Remaining queries today."""
        info = self.get_user_info()
        if info:
            return info.get("queries_remaining", DAILY_LIMIT - self._queries_today)
        return DAILY_LIMIT - self._queries_today

    def save_result(self, result: dict[str, Any], name: str) -> Path:
        """Save query result to import directory."""
        filepath = IMPORT_DIR / f"{name}.json"
        with open(filepath, "w") as f:
            json.dump(result, f, indent=2)
        return filepath

    def import_query(self, query_key: str, output_name: str, **kwargs) -> Path:
        """Execute a predefined query and save result."""
        sql = QUERIES[query_key].format(**kwargs)
        result = self.query_formatted(sql)
        output = {
            "query": sql,
            "count": len(result),
            "rows": result,
        }
        return self.save_result(output, output_name)


class QuotaExceededError(Exception):
    pass


class RateLimitError(Exception):
    pass
