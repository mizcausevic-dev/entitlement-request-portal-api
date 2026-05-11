# Architecture

## Purpose

This project models entitlement handling as an API-driven governance workflow instead of a generic CRUD app. The focus is on:

- access sensitivity
- approval staging
- policy drag
- escalation outcomes

## Structure

- `src/Data/` holds the sample request inventory
- `src/Services/` contains scoring and summary logic
- `src/Http/` exposes the JSON surface
- `tests/run_tests.php` provides lightweight verification without a test framework dependency

## Design Choices

- Dependency-free PHP keeps setup minimal and portable.
- The service layer separates request data from scoring logic.
- The API is intentionally small, but it still proves a realistic entitlement review surface.

## Extension Paths

- replace sample data with a database-backed request store
- add approval comments and audit timelines
- layer in ownership groups and request templates
- add policy packs for privileged admin, contractor access, and temporary exception flows
