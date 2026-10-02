import json
import sys


def token_set(value):
    return {
        token.strip(".,:;!?()[]{}").lower()
        for token in str(value or "").split()
        if len(token.strip()) >= 3
    }


def score(current, candidate):
    value = 0.0

    if (
        current.get("education_level")
        and current.get("education_level")
        == candidate.get("education_level")
    ):
        value += 5.0

    current_price = float(
        current.get("price_per_student") or 0
    )
    candidate_price = float(
        candidate.get("price_per_student") or 0
    )

    if current_price > 0 and candidate_price > 0:
        diff = abs(
            candidate_price - current_price
        ) / current_price
        value += max(0.0, 3.0 - diff * 3.0)

    overlap = (
        token_set(current.get("title"))
        & token_set(candidate.get("title"))
    )

    value += min(len(overlap), 3)

    return round(value, 3)


def main():
    payload = json.loads(sys.stdin.buffer.read().decode("utf-8-sig"))

    current = payload.get("current", {})
    candidates = payload.get("candidates", [])

    ranked = []

    for item in candidates:
        row = dict(item)
        row["score"] = score(current, row)
        ranked.append(row)

    ranked.sort(
        key=lambda item: item["score"],
        reverse=True
    )

    print(
        json.dumps(
            ranked[:3],
            ensure_ascii=False
        )
    )


if __name__ == "__main__":
    main()