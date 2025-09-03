# Calendar API

The calendar endpoint lets doctors and admins query surgeries scheduled in a specific room.

```bash
GET /calendar?room_number=1&start_date=2025-01-01&end_date=2025-01-31
```

Parameters:
- `room_number`: required integer between 1 and 8.
- `start_date` and `end_date`: required dates (end must be on or after start).

The response is JSON ordered by date and start time and includes the `id`, `date`, `start_time`, `end_time`, `patient_name`, and
 `procedure` fields.

When submitting surgery requests, overlaps are only validated against other surgeries in the **same room** on the same date.
Concurrent surgeries in different rooms are accepted.

For example, both of these requests succeed even though their times overlap:

```bash
POST /surgery-requests
{ "date": "2025-01-10", "start_time": "10:00", "end_time": "11:00", "room_number": 1, ... }

POST /surgery-requests
{ "date": "2025-01-10", "start_time": "10:00", "end_time": "11:00", "room_number": 2, ... }
```

## Create reservation

```bash
POST /surgery-requests
{
  "date": "2025-01-10",
  "start_time": "10:00",
  "end_time": "11:00",
  "room_number": 1,
  "duration_minutes": 60,
  "patient_name": "John Doe",
  "procedure": "Appendectomy"
}
```

**Response**
```json
{
  "ok": "Solicitação criada!"
}
```

## Confirm reservation

```bash
POST /surgery-requests/{id}/approve
```

**Response**
```json
{
  "ok": "Solicitação aprovada!"
}
```

## Cancel reservation

```bash
DELETE /surgery-requests/{id}
```

If the user can delete the request, the record is removed; otherwise the status is updated to `cancelled`.

**Response**
```json
{
  "ok": "Solicitação cancelada."
}
```
