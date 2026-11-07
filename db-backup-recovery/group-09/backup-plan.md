# Database Backup Plan

**Project:** Batumbakal Gym Membership Management System  
**Database:** `grp99_gymdb`  
**PostgreSQL Version:** 18.6

## Backup Configuration

| Property | Configuration |
|---|---|
| Database | `grp99_gymdb` |
| Backup utility | `pg_dump` |
| Backup format | Custom (`-F c`) |
| Backup filename | `batumbakal_activity6.dump` |
| Backup location | Batumbakal-Gym project directory |
| Backup method | Manual database backup |

## Backup Command

The following command was used to create the database backup:

```powershell
pg_dump -U postgres -d grp99_gymdb -F c -f .\batumbakal_activity6.dump