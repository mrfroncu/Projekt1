# System Zarządzania Zgłoszeniami (Ticket System)

System do obsługi zgłoszeń technicznych z trzema typami kont: **użytkownik**, **dispatcher**, **support**. W pełni oparty o PHP, MySQL oraz TailwindCSS.

## Funkcjonalności

### Role użytkowników:

- **Użytkownik**
  - Tworzy nowe zgłoszenia (ticket)
  - Dodaje tytuł, opis, priorytet i załącznik
  - Może pisać odpowiedzi w wątku i przeglądać historię rozmowy
  - Zamykana zgłoszenia
  - Podgląd statusów i załączników

- **Dispatcher**
  - Widzi wszystkie zgłoszenia
  - Przypisuje ticket do supportu
  - Może edytować priorytet i temat zgłoszenia
  - Dashboard ze statystykami (liczba rozwiązanych, czas reakcji, wykresy)

- **Support**
  - Odpowiada na przypisane zgłoszenia
  - Zmienia status: otwarte → w trakcie → oczekuje na odpowiedź → rozwiązane/zamknięte
  - Może dodawać załączniki w odpowiedziach
  - Widzi całą historię wątku i załączniki

## Technologie

- **PHP 8.x**
- **MySQL 5.x/8.x**
- **HTML5, JavaScript (Fetch API)**
- **TailwindCSS (CDN)**
- **Chart.js** – wykresy statystyk

## Struktura katalogów

projekt/
├── backend/
│   ├── auth/
│   ├── dispatcher/
│   ├── tickets/
│   └── config.php
├── frontend/
│   ├── user/
│   ├── support/
│   └── dispatcher/
├── uploads/
│   └── messages/{message_id}/
├── database.sql
└── README.md

## Instalacja

1. Sklonuj repozytorium:
   git clone https://github.com/twoja-nazwa/ticket-system.git

2. Wgraj pliki na serwer lub uruchom lokalnie z XAMPP / Laragon / Docker

3. Utwórz bazę danych i zaimportuj plik:
   database.sql

4. Skonfiguruj połączenie z bazą danych w backend/config.php:
   $pdo = new PDO("mysql:host=localhost;dbname=nazwa_bazy", "user", "haslo");

5. Upewnij się, że katalog uploads/ ma uprawnienia zapisu:
   chmod -R 777 uploads/

6. Uruchom aplikację:
   - Użytkownik: /frontend/user/login.php
   - Dispatcher: /frontend/dispatcher/login.php
   - Support: /frontend/support/login.php

## Testowe konta

Użytkownik:
  login: user
  hasło: user

Dispatcher:
  login: dispatcher
  hasło: dispatcher

Support:
  login: support
  hasło: support

## Autor

Projekt stworzony przez Froncalke.

## Licencja

MIT © 2025
