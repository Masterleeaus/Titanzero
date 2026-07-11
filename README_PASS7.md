# Titan Zero Pass 7 — Standards-grounded Assist (No AI)
This pass wires the standards retrieval layer into a deterministic assist endpoint.

## Endpoint
POST /titan-zero/assist/standards

Payload:
- question: string
- page_context: object

Response:
- cards[] (text + citations)
