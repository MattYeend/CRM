# CRM System
A Laravel 12 CRM system

<!-- TOC -->
## Table of Contents
1. [Tech Stack](#tech-stack)
2. [General Information](#general-information)
    1. [Key Highlights](#key-highlights)
    2. [Core Features](#core-features)
3. [How To Setup](#how-to-setup)
4. [How To Contribute](#how-to-contribute)
    1. [Commit Conventions](#commit-conventions)
    2. [Maintainer Merge Strategy](#maintainer-merge-strategy)
5. [General CLI Commands](#general-cli-commands)
6. [Specific CLI Commands](#specific-cli-commands)
7. [Sponsor The Project](#sponsor-the-project)
<!-- /TOC -->

---

## Tech Stack

| Tech | Version |
|------|---------|
| PHP | 8.4.6 |
| Laravel Installer | 5.14.0 |
| Laravel | 12.28.1 |
| Composer | 2.8.8 |
| NPM | 11.5.2 |
| Node | v23.11.0 |
| VueJS | 3.5.18 |
| MySQL | 8.0.42 |

---

## General Information
This project is an all-in-one CRM system designed to help businesses manage customers, leads, and internal workflows from a single platform.
It is built with Laravel 12, following common Laravel OSS conventions, with an emphasis on clean architecture, extensibility, and long-term maintainability.

### Key Highlights
- Built in Laravel 12 - leveraging the latest framework features for performance, security, and scalability
- Modular & Extensible - easily add new modules or integrate with external APIs
- User-Friendly Interface - modern, responsive design for smooth navigation and usability

### Core Features
- Customer & Lead Management: Organise contacts, track leads, and maintain detailed profiles
- Role-Based Access Control: Secure user management with customizable permissions
- Analytics & Reporting: Gain insights into business performance with dynamic dashboards

---

## How To Setup

Follow these steps to set up the project locally:

1. Clone the repository
```bash
git clone https://github.com/MattYeend/CRM.git
cd CRM
```
2. Install PHP dependencies
```bash
composer install
```
3. Install Node dependencies
```bash
npm install && npm run build
```
4. Set up environment
```bash
cp .env.example .env
php artisan key:generate
```
5. Configure your database in `.env` and run migrations:
```bash
php artisan migrate
```
6. Seed all tables if needed:
```bash
php artisan seed
```
7. Set up storage
```bash
php artisan storage:link
```
8. Run the development servers
```bash
php artisan serve
npm run dev
```

---

## How To Contribute
This project follows the standard Laravel OSS fork-and-pull-request workflow, used by most open-source Laravel packages and applications.

1. Fork the repository.
2. Create a new branch: `git checkout -b feature/your-feature-name`.
3. Make your changes and commit: `git commit -m '#issue-number Add your message here'`.
4. Run `php artisan insights` and make any relevant changes that it might suggest.
5. Ensure that the relevant language file(s) have been created.
6. Ensure there's relevant tests and that they work and pass.
7. If anything requires `vue.js` changes, run: `npm i && npm run build`.
8. Push to your fork: `git push origin feature/your-feature-name`.
9. Create a Pull Request.

Please follow the code style and commit message conventions.

---

### Commit Conventions
To keep the commit history clean and consistent, please follow these conventions:
```graphql
#issue-number Short, clear description in the imperative mood
```

Examples
```graphql
#42 Add customer export feature
#87 Fix validation for lead creation
#101 Refactor role permission checks
```

Guidelines:
- Reference an issue number where applicable
- Use the imperative mood (“Add”, not “Added”)
- Keep commits focused and descriptive
- Avoid bundling unrelated changes into a single commit
Maintainers may squash commits on merge.

---

### Maintainer Merge Strategy
For clarity and transparency:
- External contributors do not merge directly
- All changes enter the project via Pull Requests
- Pull Requests are reviewed before merging
- The preferred merge method is Squash and Merge
- Keeps `main` and `develop` history clean
- One commit per feature or fix
- Commit message may be edited by maintainers
The `main` and `develop` branchs is protected and should never be pushed to directly.

--- 

## General CLI Commands

| Command | Description |
| --- | --- |
| `php artisan make:model modelName -mcr` | Create a model, migration, and resource controller |
| `php artisan make:model modelName -a` or `php artisan make:model modelName --all` | Create a model, migration, factory, seeder, controller, resource, request(s) |
| `php artisan make:model modelName` | Create a model |
| `php artisan make:controller controllerName` | Create a controller |
| `php artisan make:controller controllerName --resource` | Create a resource controller |
| `php artisan make:migration migration_name` | Create a migration |
| `php artisan make:seeder SeederName` | Create a seeder |
| `php artisan make:factory FactoryName` | Create a factory |
| `php artisan make:request RequestName` | Creates a form request for validation |
| `php artisan make:event EventName` | Creates an event class |
| `php artisan make:listener ListenerName` | Creates a listener class |
| `php artisan make:job JobName` | Creates a queued job |

--- 

## Specific CLI Commands

| Command | Description | 
| --- | --- |
| `php artisan make:service ServiceName` | Creates a new service class |
| `php artisan permission:clear` | Clear permissions if changed |
| `php artisan insights` | Run insights package | 

---

## Sponsor The Project
If you find this project useful, consider sponsoring it to support future development and maintenance.<br>
<a href="https://www.buymeacoffee.com/mattyeend">☕ Buy Me a Coffee</a><br>
<a href="https://github.com/sponsors/MattYeend">💸 Personal GitHub Sponsor</a><br>
<a href="https://github.com/sponsors/MatthewYeend">🏢 Company Github Sponsor</a>
