<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config/db_config.php'; ?>

<!DOCTYPE html>
<html lang="it">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager Pro | Zen Productivity</title>

    <meta name="description" content="Task Manager Pro: organizza le tue attività quotidiane con semplicità. Crea liste, gestisci task e aumenta la tua produttività.">
    <meta name="keywords" content="task manager, to-do list, produttività, organizzazione, gestione attività">
    <meta name="author" content="Elena Cazzaniga">
    <meta name="robots" content="index, follow">

    <!-- favicon TODO -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">

    <!-- performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">


    <!-- font e librerie -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Plus+Jakarta+Sans:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!--  OPEN GRAPH (anteprima su social e link) da aggiungere una volta terminata l'app -->


    <!-- file css esterno -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>
    <div class="d-none" id="guest-banner"></div>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="brand-icon me-2">🍃</span>
                <span class="fw-bold">TaskZen</span>
            </a>

            <div class="ms-auto dropdown">
                <button class="btn btn-user dropdown-toggle d-flex align-items-center" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar me-2">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <span class="d-none d-sm-inline">Account</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="javascript:void(0)" id="nav-login-btn"><i class="bi bi-door-open me-2"></i>Accedi</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" id="nav-signup-btn"><i class="bi bi-pencil-square me-2"></i>Registrati</a></li>
                    <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item fw-bold text-accent" href="#"><i class="bi bi-star me-2"></i>Passa a Pro</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 mt-2">
        <div class="row mb-5 align-items-center">
            <div class="col-md-8">
                <h2 class="display-6 fw-bold mb-1">Benvenuta nella tua calma.</h2>
                <p class="text-muted">Organizza i tuoi flussi di lavoro senza stress.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-svuota-zen" id="btn-empty-all">
                    <i class="bi bi-trash3 me-1"></i> Svuota Lavagna
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="sticky-top-custom">

                    <div class="card card-zen mb-4">
                        <div class="card-body p-4">
                            <h6 class="card-label mb-3">NUOVA AREA</h6>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control form-control-zen" id="list-title" placeholder="Es. Lavoro, Casa..." required>
                                <button class="btn btn-add-list" id="btn-add-list">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card card-zen shadow-sm">
                        <div class="card-body p-4">
                            <h6 class="card-label mb-4">DETTAGLI TASK</h6>
                            <form id="book-form">
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-zen" id="task-title" placeholder="Titolo dell'attività" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control form-control-zen" id="task" rows="2" placeholder="Breve descrizione..."></textarea>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <input type="date" class="form-control form-control-zen" id="date-max" required>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-select form-control-zen" id="priorita">
                                            <option value="urgente">Urgente</option>
                                            <option value="media">Media</option>
                                            <option value="bassa">Bassa</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <select class="form-select form-control-zen" id="lista-riferimento" required>
                                        <option value="">Destinazione...</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-zen-primary w-100 py-2">
                                    Conferma Task
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-4" id="container-liste">
                </div>
            </div>
        </div>
    </main>


    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold" id="authTitle">Bentornato</h3>
                        <p class="text-muted" id="authSubtitle">Inserisci le tue credenziali per continuare</p>
                    </div>
                    <form id="auth-form">
                        <div id="group-nome" class="mb-3" style="display: none;">
                            <label class="form-label small fw-bold">NOME</label>
                            <input type="text" id="auth-nome" class="form-control form-control-zen" placeholder="Il tuo nome">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="auth-email">EMAIL</label>
                            <input type="email" class="form-control form-control-zen" id="auth-email" required autocomplete="email" placeholder="name@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="auth-password">PASSWORD</label>
                            <input type="password" class="form-control form-control-zen" id="auth-password" minlength="8" required placeholder="••••••••">
                            <div id="password-strength" class="form-text mt-1" style="font-size: 0.75rem;"></div>
                        </div>


                        <div id="confirm-password-group" class="mb-3" style="display: none;">
                            <label class="form-label small fw-bold">CONFERMA PASSWORD</label>
                            <input type="password" id="auth-password-confirm" class="form-control form-control-zen" placeholder="Ripeti password">
                        </div>

                        <div id="terms-group" class="mb-3 form-check" style="display: none;">
                            <input type="checkbox" class="form-check-input" id="auth-terms">
                            <label class="form-check-label small" for="auth-terms">
                                Accetto i <a href="#" class="text-decoration-none">Termini di Utilizzo</a> e la Privacy Policy
                            </label>
                        </div>

                        <div class="g-recaptcha mb-3" id="recaptcha-group" data-sitekey="6LfSaOYsAAAAAIQVoQzpkySQRxA-0ZE0sYtt8zYy" style="display: none;"></div>

                        <button type="submit" class="btn btn-verde-zen w-100" id="btn-auth-submit">Accedi</button>
                        <div class="text-center mt-3">
                            <a href="javascript:void(0)" class="small text-accent text-decoration-none fw-bold" id="toggleAuthMode">Non hai un account? Registrati</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- async — scarica lo script in parallelo senza bloccare la pagina --- defer — esegue lo script solo dopo che il DOM è pronto -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/ui.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>