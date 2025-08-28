# Calendar API

The calendar endpoint lets doctors and admins query surgeries scheduled in a specific room.

```bash
GET /calendar?room_number=1&start_date=2025-01-01&end_date=2025-01-31
```

Parameters:
- `room_number`: required integer between 1 and 9.
- `start_date` and `end_date`: required dates (end must be on or after start).

The response is JSON ordered by date and start time and includes the `id`, `date`, `start_time`, `end_time`, `patient_name`, and `procedure` fields.

When submitting surgery requests, the same room and date rules are validated and any overlapping schedule—regardless of room—triggers a validation error describing the conflicting room and times.

