# 🗂️ Task Manager Pro — Zen Productivity

Applicazione web Full Stack per la gestione di task e liste personali, 
con sistema di autenticazione utente e architettura asincrona.

> 🚧 Progetto in sviluppo attivo

## ✨ Funzionalità

- ✅ Registrazione e login utente con sessioni PHP
- ✅ Password hashata con `password_hash()` (bcrypt)
- ✅ Creazione, modifica ed eliminazione task
- ✅ Organizzazione task in liste personalizzate
- ✅ Salvataggio dati su database MySQL per utenti registrati
- ✅ Modalità ospite con salvataggio in localStorage
- ✅ Interfaccia responsive con Bootstrap 5
- 🔄 Rinomina liste con persistenza (in sviluppo)
- 🔄 Filtri e ricerca task (in sviluppo)

## 🛠️ Tecnologie

| Area | Tecnologie |
|------|-----------|
| Frontend | HTML5, CSS3, JavaScript ES6+, Bootstrap 5 |
| Backend | PHP 8, PDO |
| Database | MySQL |
| Architettura | AJAX, REST API, Sessioni PHP |
| Sicurezza | password_hash, prepared statements, session_regenerate_id |

## 📁 Struttura del Progetto

task-manager-fullstack/
├── api/
│   ├── auth.php          # Registrazione e login
│   ├── get_tasks.php     # Lettura task dal DB
│   ├── save_task.php     # Creazione e modifica task
│   ├── delete_task.php   # Eliminazione task
│   └── logout.php        # Distruzione sessione
├── assets/
│   ├── css/style.css     # Stili personalizzati
│   └── js/
│       ├── app.js        # Logica principale
│       ├── ui.js         # Gestione interfaccia
│       └── api.js        # Chiamate AJAX
├── config/
│   └── db_config.php     # Configurazione DB (escluso da Git)
├── database/
│   └── schema.sql     # Schema del database
├── index.php             # Entry point dell'applicazione
├── .env                  # Variabili d'ambiente (escluso da Git)
└── .gitignore            # File esclusi da Git

## 🚀 Installazione locale

1. Clona il repository
```bash
git clone https://github.com/CazzanigaDev/task-manager-fullstack.git
```

2. Importa il database — crea un DB MySQL chiamato `task_manager_db` 
   e importa lo schema delle tabelle

3. Crea il file `config/db_config.php` con le tue credenziali:
```php
<?php
$host = "localhost";
$dbname = "task_manager_db";
$user = "il_tuo_utente";
$password = "la_tua_password";
```

4. Crea il file `.env` nella root:
RECAPTCHA_SITE_KEY=la_tua_chiave
RECAPTCHA_SECRET_KEY=la_tua_chiave_segreta

5. Avvia con XAMPP e apri `http://localhost/task-manager-fullstack`

## 👩‍💻 Autrice

Elena Cazzaniga — Junior Full Stack Developer  
[LinkedIn](https://www.linkedin.com/in/cazzaniga-elena)
