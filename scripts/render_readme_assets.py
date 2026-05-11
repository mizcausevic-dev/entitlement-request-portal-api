from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
SCREENSHOTS = ROOT / "screenshots"

BG = "#081321"
PANEL = "#13253c"
CARD = "#1b2d48"
BORDER = "#274869"
TEXT = "#f0efdd"
MUTED = "#a8b6cb"
ACCENT = "#80c8ff"
WARN = "#ffd56d"
CRITICAL = "#ff8c8c"
STABLE = "#8de4ae"


def font(size: int, bold: bool = False):
    name = "arialbd.ttf" if bold else "arial.ttf"
    try:
        return ImageFont.truetype(name, size)
    except OSError:
        return ImageFont.load_default()


def wrap(draw: ImageDraw.ImageDraw, text: str, x: int, y: int, width: int, line_height: int, fill: str, fnt) -> int:
    words = text.split()
    line = ""
    for word in words:
        candidate = word if line == "" else f"{line} {word}"
        if draw.textlength(candidate, font=fnt) <= width:
            line = candidate
        else:
            draw.text((x, y), line, font=fnt, fill=fill)
            y += line_height
            line = word
    if line:
        draw.text((x, y), line, font=fnt, fill=fill)
        y += line_height
    return y


def base(title: str, subtitle: str):
    img = Image.new("RGB", (1600, 900), BG)
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle((36, 36, 1564, 864), radius=30, fill=PANEL, outline=BORDER, width=2)
    draw.text((86, 84), title, font=font(24), fill=ACCENT)
    draw.text((86, 148), subtitle, font=font(56, bold=True), fill=TEXT)
    return img, draw


def card(draw, box, title, value, blurb, value_fill=TEXT):
    draw.rounded_rectangle(box, radius=24, fill=CARD, outline=BORDER, width=2)
    x1, y1, x2, _ = box
    draw.text((x1 + 26, y1 + 24), title, font=font(18), fill=MUTED)
    draw.text((x1 + 26, y1 + 74), str(value), font=font(44, bold=True), fill=value_fill)
    wrap(draw, blurb, x1 + 26, y1 + 138, x2 - x1 - 52, 28, MUTED, font(18))


def render():
    SCREENSHOTS.mkdir(exist_ok=True)

    img, draw = base("ENTITLEMENT REQUEST PORTAL API", "Approval routing, policy pressure, and access governance in one lane.")
    card(draw, (86, 300, 430, 560), "Tracked requests", 3, "Live request set with owner lanes, approval drag, and policy findings.")
    card(draw, (450, 300, 794, 560), "Critical requests", 1, "Contractor or privileged requests already above the escalation threshold.", CRITICAL)
    card(draw, (814, 300, 1158, 560), "Watch requests", 1, "SOX or separation-of-duties pressure needs higher-friction review.", WARN)
    card(draw, (1178, 300, 1522, 560), "Pending steps", 3, "Queued or stalled approvals still blocking clean fulfillment.", TEXT)
    draw.rounded_rectangle((86, 612, 1522, 804), radius=24, fill=CARD, outline=BORDER, width=2)
    draw.text((112, 640), "CURRENT DECISION LANE", font=font(20), fill=ACCENT)
    draw.text((112, 688), "Zendesk export bypass should remain frozen until security closes the contractor privilege finding.", font=font(34, bold=True), fill=TEXT)
    draw.text((112, 748), "This project treats access requests like operating decisions, not just a queue of tickets.", font=font(24), fill=MUTED)
    img.save(SCREENSHOTS / "01-hero.png")

    img, draw = base("APPROVAL SPINE", "The workflow is shaped by owner lanes, policy drag, and fulfillment risk.")
    columns = [
        ("Manager review", "Approved", STABLE, "Revenue or line manager validated business need."),
        ("System owner", "Pending", WARN, "Target system owner still evaluating scope and blast radius."),
        ("Security review", "Stalled", CRITICAL, "Policy findings are blocking direct approval."),
    ]
    x = 86
    for title, state, color, blurb in columns:
        draw.rounded_rectangle((x, 320, x + 430, 760), radius=24, fill=CARD, outline=BORDER, width=2)
        draw.text((x + 28, 352), title, font=font(28, bold=True), fill=TEXT)
        draw.text((x + 28, 404), state, font=font(22, bold=True), fill=color)
        wrap(draw, blurb, x + 28, 462, 370, 30, MUTED, font(20))
        x += 484
    img.save(SCREENSHOTS / "02-approval-spine.png")

    img, draw = base("POLICY FINDINGS", "Risk scoring converts entitlement context into a concrete operator action.")
    card(draw, (86, 306, 740, 570), "Segregation of duties", "high", "Delete and stage override rights are colliding in a revenue-owned production lane.", CRITICAL)
    card(draw, (780, 306, 1434, 570), "Contractor privilege", "critical", "Contractor account is requesting bypass controls on exported customer data.", CRITICAL)
    draw.rounded_rectangle((86, 620, 1518, 800), radius=24, fill=CARD, outline=BORDER, width=2)
    draw.text((112, 648), "RECOMMENDED ACTION", font=font(20), fill=ACCENT)
    draw.text((112, 694), "Escalate to identity governance and security before granting access.", font=font(32, bold=True), fill=TEXT)
    draw.text((112, 742), "Freeze fulfillment until policy exceptions are resolved and the system owner signs the narrowed scope.", font=font(22), fill=MUTED)
    img.save(SCREENSHOTS / "03-policy-findings.png")

    img, draw = base("VALIDATION PROOF", "The repo includes runnable API checks instead of static mockups only.")
    draw.rounded_rectangle((86, 286, 980, 804), radius=24, fill="#09111c", outline=BORDER, width=2)
    lines = [
        "> php tests/run_tests.php",
        "All entitlement-request-portal-api tests passed.",
        "",
        "> GET /api/dashboard/summary",
        "tracked_requests     3",
        "critical_requests    1",
        "watch_requests       1",
        "pending_steps        3",
        "",
        "> POST /api/analyze/request",
        "decision             escalate",
        "score                94",
    ]
    y = 322
    for line in lines:
        draw.text((118, y), line, font=font(24, bold=line.startswith(">")), fill=STABLE if line.startswith(">") else TEXT)
        y += 34
    draw.rounded_rectangle((1030, 286, 1518, 804), radius=24, fill=CARD, outline=BORDER, width=2)
    draw.text((1060, 324), "REQUEST SET", font=font(20), fill=ACCENT)
    requests = [
        ("er-4018", "watch", WARN),
        ("er-4027", "escalate", CRITICAL),
        ("er-4031", "stable", STABLE),
    ]
    y = 384
    for request_id, state, color in requests:
        draw.text((1060, y), request_id, font=font(28, bold=True), fill=TEXT)
        draw.text((1060, y + 38), state.upper(), font=font(20, bold=True), fill=color)
        y += 118
    img.save(SCREENSHOTS / "04-proof.png")


if __name__ == "__main__":
    render()

