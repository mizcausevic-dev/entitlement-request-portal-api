from __future__ import annotations

import json
import subprocess
import sys
import time
import urllib.request
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
PHP = Path(r"C:\Users\chaus\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe")
PORT = 4485


def get_json(path: str):
    with urllib.request.urlopen(f"http://127.0.0.1:{PORT}{path}") as response:
        return json.loads(response.read().decode("utf-8"))


def post_json(path: str, payload: dict):
    request = urllib.request.Request(
        f"http://127.0.0.1:{PORT}{path}",
        data=json.dumps(payload).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    with urllib.request.urlopen(request) as response:
        return json.loads(response.read().decode("utf-8"))


def main() -> None:
    process = subprocess.Popen(
        [str(PHP), "-S", f"127.0.0.1:{PORT}", "router.php"],
        cwd=ROOT,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    try:
        time.sleep(2)
        summary = get_json("/api/dashboard/summary")
        if summary["summary"]["tracked_requests"] != 3:
            raise SystemExit("Unexpected tracked_requests count")
        detail = get_json("/api/requests/er-4027")
        if detail["status"] != "escalate":
            raise SystemExit("Unexpected request state")
        analysis = post_json(
            "/api/analyze/request",
            {
                "sensitivity": "critical",
                "privileged": True,
                "sox_scoped": True,
                "contractor_access": True,
                "approval_lag_hours": 12,
            },
        )
        if analysis["decision"] != "escalate":
            raise SystemExit("Unexpected analysis decision")
    finally:
        process.terminate()
        process.wait(timeout=10)


if __name__ == "__main__":
    main()

