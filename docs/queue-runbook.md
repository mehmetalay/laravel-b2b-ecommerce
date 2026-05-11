# Queue Runbook (Admin Exports)

## Scope
This runbook defines the operational baseline for the admin export queue in this Laravel 8 application.

It covers export jobs for:
- Products
- Orders
- Payments

Queue driver assumption:
- `QUEUE_CONNECTION=database`

## Export Lifecycle
1. The frontend sends an export request to the admin export endpoint.
2. The backend creates an `export_jobs` record with `status=pending`.
3. `RunExportJob` is picked up by a queue worker and moved to `processing`.
4. The job ends in one of two terminal states:
- `completed` with output metadata such as file path/name
- `failed` with an error payload
5. The frontend polls export state via:
- `GET /admin/api/exports/{exportJob}`

## Local Development
1. Confirm the queue connection:
```bash
php artisan tinker --execute="echo config('queue.default');"
```
2. Start a local worker:
```bash
php artisan queue:work --queue=default --tries=1 --sleep=1
```
3. Monitor queue load:
```bash
php artisan queue:monitor default --max=1
```

## Production Worker Baseline
- Queue workers must remain continuously running.
- After each deployment, restart workers to load fresh code:
```bash
php artisan queue:restart
```
- Validate failed jobs and queue pressure regularly:
```bash
php artisan queue:failed
php artisan queue:monitor default --max=1
```

## Supervisor Example
Use process supervision in production so workers are restarted automatically.

```ini
[program:app-queue-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work --queue=default --sleep=1 --tries=1 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/app-queue-default.log
stopwaitsecs=3600
```

## Common Failure Cases
- Worker stopped: `jobs` backlog grows and `export_jobs` remain `pending`.
- Failed job: `export_jobs.status=failed` with populated error details.
- File/path permission issue: export may complete logically but output is not readable or downloadable.
- Polling timeout: frontend waits indefinitely while no worker is actively processing jobs.

## Quick Diagnostics
1. Check active queue driver:
```bash
php artisan tinker --execute="echo config('queue.default');"
```
2. Check queue pressure:
```bash
php artisan queue:monitor default --max=1
```
3. Inspect failed jobs:
```bash
php artisan queue:failed
```

## Operational Notes
- Before changing application code, first validate queue worker health and runtime status.
- Export polling issues are frequently caused by worker/runtime conditions rather than business-logic defects.

## Potential Improvements
- Better user-facing timeout message with clear operator guidance
- Queue health indicator in the admin panel
- Export retry action for failed jobs