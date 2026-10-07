"""Legacy CLI wrapper for the DT-17 recommender.

Reads JSON from stdin and delegates to app.recommender.goi_y so the
project has one canonical recommendation algorithm.
"""

from __future__ import annotations

import json
import sys

from app.recommender import goi_y


def main() -> None:
    payload = json.load(sys.stdin)
    current = payload.get('current') or {}
    program_id = payload.get('program_id') or current.get('id')
    if program_id is None:
        raise SystemExit('program_id is required')
    k = int(payload.get('k') or 6)
    print(json.dumps(
        {'program_id': int(program_id), 'items': goi_y(int(program_id), k)},
        ensure_ascii=False,
    ))


if __name__ == '__main__':
    main()
