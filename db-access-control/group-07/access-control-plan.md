**# Group 07 – Applicant Management System**

**## Activity 4: Database Access Control Plan**

**Project:** Group 07 - Applicant Management System
**Database:** `grp07_applicantdb`
**Schema:** `public`
**Database Owner:** `postgres`

## Existing Roles

The following group roles already exist:

- app_admin_grp (NOLOGIN)
- app_runtime_grp (NOLOGIN)
- app_readonly_grp (NOLOGIN)

## Role Membership Findings

Existing memberships:

- app_admin_grp is a member of app_runtime_grp.
- pawpaw_app is a member of app_runtime_grp.
- app_runtime is a member of app_runtime_grp.

No additional role memberships were created.

## Access Control Findings

- Database owner is postgres.
- Public schema owner is pg_database_owner.
- PUBLIC has USAGE but not CREATE on the public schema.
- No explicit database ACL entries were found.
- No explicit table or sequence privileges were found.
- The three group roles have no effective privileges on public.applicant.

## Result

Existing roles were inspected. The expected group roles exist, but the inspected applicant table has no effective privileges granted to them.