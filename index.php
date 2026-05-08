<?php require_once 'config/db_config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager Pro | Organizza il tuo lavoro efficacemente</title>
    <meta name="description" content="Gestisci i tuoi task quotidiani con facilità. Task Manager Full Stack creato con PHP e MySQL per la massima produttività.">
    <meta name="keywords" content="gestione task, to-do list online, produttività, PHP, MySQL, task manager professionale">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body { background-color: #f8f9fa; }
        .card-libro { transition: transform 0.2s; }
        .card-libro:hover { transform: scale(1.02); }
        .alert-success {
            border: none; border-left: 5px solid #28a745;
            background-color: #d4edda; color: #155724;
            border-radius: 8px; font-weight: 500; display: none;
        }
        .fade-in { animation: slideIn 0.5s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">📚 La mia lavagna dei task</span>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h3>To Do List</h3>
                <button id="clear-all" class="btn btn-outline-danger btn-sm">Svuota Lavagna</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="sticky-top" style="top: 20px; z-index: 1020;">
                    
                    <div id="success-alert" class="alert alert-success alert-dismissible fade-in shadow-sm mb-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> Task salvato correttamente! 🎉
                        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
                    </div>

                    <div class="card shadow-sm border-start border-secondary border-1 mb-3">
                        <div class="card-body">
                            <h6>La tua Lista</h6>
                            <div class="mb-3">
                                <label for="list-title" class="form-label">Titolo Lista</label>
                                <input type="text" class="form-control" id="list-title" required>
                            </div>
                            <button class="btn btn-success w-100" onclick="aggiungiLista()">
                                Aggiungi Lista <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card shadow-sm border-start border-secondary border-1">
                        <div class="card-body">
                            <form id="book-form">
                                <h6>Il tuo Task</h6>
                                <div class="mb-3">
                                    <label for="task-title" class="form-label">Titolo Task</label>
                                    <input type="text" class="form-control" id="task-title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="task" class="form-label">Descrizione</label>
                                    <input type="text" class="form-control" id="task" required>
                                </div>
                                <div class="mb-3">
                                    <label for="date-max" class="form-label">Scadenza</label>
                                    <input type="date" class="form-control" id="date-max" required>
                                </div>
                                <div class="mb-3">
                                    <label for="priorita" class="form-label">Priorità</label>
                                    <select class="form-select" id="priorita">
                                        <option value="urgente e importante">Urgente e importante</option>
                                        <option value="urgente ma non importante">Urgente ma non importante</option>
                                        <option value="importante ma non urgente">Importante ma non urgente</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="lista-riferimento" class="form-label">Assegna alla Lista</label>
                                    <select class="form-select" id="lista-riferimento" required>
                                        <option value="">Scegli una lista...</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Salva Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row" id="container-liste"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>