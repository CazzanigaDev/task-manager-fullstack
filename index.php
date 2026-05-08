<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esercizio finale - Lavagna To Do</title>
    <script src="https://kit.fontawesome.com/19ab035db3.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }


        .card-libro {
            transition: transform 0.2s;
            
        }

        .card-libro:hover {
            transform: scale(1.02);
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

                    <div class="card shadow-sm border-start border-secondary border-1 mb-3">
                        <div class="card-body">
                            <h6>La tua Lista</h6>
                            <div class="mb-3">
                                <label for="list-title" class="form-label">List-Title</label>
                                <input type="text" class="form-control" id="list-title" required>
                            </div>
                            <button class="btn btn-success w-100" onclick="aggiungiLista()">
                                Aggiungi <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card shadow-sm border-start border-secondary border-1">
                        <div class="card-body">
                            <form id="book-form">
                                <h6>Il tuo Task</h6>
                                <div class="mb-3">
                                    <label for="task-title" class="form-label">Task Title</label>
                                    <input type="text" class="form-control" id="task-title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="task" class="form-label">Testo Task</label>
                                    <input type="text" class="form-control" id="task" required>
                                </div>
                                <div class="mb-3">
                                    <label for="date-max" class="form-label">Data di scadenza</label>
                                    <input type="date" class="form-control" id="date-max" required>
                                </div>
                                <div class="mb-3">
                                    <label for="priorita" class="form-label">Priorità</label>
                                    <select class="form-select" id="priorita">
                                        <option value="scegli la priorità">Scegli la priorità</option>
                                        <option value="urgente e importante">Urgente e importante</option>
                                        <option value="urgente ma non importante">Urgente ma non importante</option>
                                        <option value="importante ma non urgente">Importante ma non urgente</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="lista-riferimento" class="form-label">Assegna alla Lista</label>
                                    <select class="form-select" id="lista-riferimento">
                                        <option value="">Scegli una lista</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Salva Task</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-md-8">
                <div class="row" id="container-liste">

                </div>
            </div>


        </div>
    </div>

    <div style="height: 200px;"></div>



    <script>


        /**
         * clear-all
        list-title
        book-form
        task-title
        task
        date-max
        priorita
        container-liste
         * */

        const STORAGE_KEY = "miaLavagna";

        // Array globali per gestire i dati
        let nomiListe = []; // Qui salviamo i nomi delle liste (stringhe)
        let lavagna = [];   // Qui salveremo i task (oggetti)

        // Spostiamo le funzioni FUORI dal DOMContentLoaded così l'onclick può vederle

        // funzione per creare, inserire, stampare e salvare la nuova lista con il titolo inserito dall'utente
        function aggiungiLista() {
            const inputTitolo = document.getElementById("list-title").value.trim();
            // const titolo = inputTitolo.value.trim();
            // ricordiamoci che abbiamo unito le due const, semplificando il processo, potrebbe esserci ancora qualcosa da correggere nel codice ;)

            if (inputTitolo !== "") {
                // 1. Aggiungiamo il inputTitolo all'array dei nomi
                nomiListe.push(inputTitolo);

                // 2. Aggiorniamo il menu a tendina dei task
                aggiornaSelectListe();

                // --- NUOVO: Salviamo l'array aggiornato - usiamo il nome "nomiListeSalvate" per richiamarlo con facilità in seguito ---
                localStorage.setItem("nomiListeSalvate", JSON.stringify(nomiListe));

                // Stampiamo la lista (ti consiglio di creare una funzione a parte per questo, vedi sotto)
                stampaSingolaLista(inputTitolo);

                // 4. Puliamo l'input
                inputTitolo = "";
            } else {
                alert("Inserisci un nome per la lista!");
            }
        }


        // quasi lo chiamerei Option piuttosto che select ma in realtà in HTML è l'elemento select che contiene le option dell'input con opzioni a cascata.
        function aggiornaSelectListe() {
            const select = document.getElementById("lista-riferimento");

            // Puliamo e resettiamo il select - se lo lasciassimo vuoto, l'option in HTML non sarebbe visibile
            select.innerHTML = '<option value="">Scegli una lista...</option>';

            /** questa è la scrittura estesa, in realtà cambia ben poco come si nota, nella {} rimane tutto uguale
             * nomiListe.forEach(function(nome) {
                const option = document.createElement("option");
                option.value = nome;
                option.textContent = nome;
                select.appendChild(option);
            });
            */

            // Lambda per popolare le opzioni
            nomiListe.forEach(nome => {
                const option = document.createElement("option");
                option.value = nome;
                // usiamo textContent perchè è più sicuro di un semplice innerHTML per modificare il testo
                option.textContent = nome;
                select.appendChild(option);
            });
        }

        // Questa funzione si occupa SOLO di disegnare la card a schermo
        function stampaSingolaLista(titolo) {
            const container = document.getElementById("container-liste");

            // 1. Creiamo il contenitore principale (la colonna di Bootstrap)
            const col = document.createElement("div");
            col.className = "col-12 col-lg-6 mb-4";
            // Usiamo la Regex per l'ID per eliminare spazi e rendere poi l'identificazione più sicura e chiara
            col.id = `lista-${titolo.replace(/\s+/g, '')}`;

            // 2. Costruiamo la struttura interna
            // (Qui possiamo ancora usare innerHTML per il contenuto interno della card 
            // perché la card stessa è appena stata creata e "vuota")
            col.innerHTML = `
        <div class="card shadow-sm border-top border-info border-opacity-50" border-3">
            <div class="card-body">
                <h5 class="card-title text-black">${titolo}</h5>
                <hr>
                <div class="contenitore-task">
                    <p class="small text-muted italic">Nessun task in questa lista.</p>
                </div>
            </div>
        </div>`;

            // 3. Lo "appendiamo" fisicamente al contenitore nel DOM creato all'inizio della funzione
            container.appendChild(col);

        }


        // Funzione di utilità per i colori della priorità
        function getColorePriorita(priorita) {
            switch (priorita.toLowerCase()) {
                case 'urgente e importante':
                    return 'bg-danger'; // Example class for high priority
                case 'urgente ma non importante':
                    return 'bg-warning text-dark'; // Example class for medium priority
                case 'importante ma non urgente':
                    return 'bg-info text-dark'; // Example class for low priority
                default:
                    return 'bg-secondary'; // Default class
            }
        }



        function stampaSingoloTask(objTask) {
            // 1. Puntiamo alla lista giusta usando l'ID dinamico
            // qui prendiamo un const come id in cardLista ed è proprio grazie a questo che rendiamo 
            // il tutto più sicuro grazie al fatto di togliere eventuali spazi, inserire i -tra le parole- 
            // e usare una const per verificare l'id più corretto possibile
            const idCercato = "lista-" + objTask.listaRiferimento.replace(/\s+/g, '');
            const cardLista = document.getElementById(idCercato);

            if (cardLista) {
                const contenitoreTask = cardLista.querySelector(".contenitore-task");

                // Rimuoviamo il placeholder "Nessun task"
                const placeholder = contenitoreTask.querySelector("p.text-muted");
                if (placeholder) placeholder.remove();

                /** questa è la riga di codice sopra per esteso con le {} ma non servono quindi possiamo lasciare la forma contratta
                 * if (placeholder) {
                    placeholder.remove();
                }
                 * */

                // 2. Creiamo il pezzetto di LEGO (il task)
                // in realtà credo si possa creare un'ulteriore funzione per evitare la ripetizione di questa riga createElement
                const taskCard = document.createElement("div");
                taskCard.className = "card border-light shadow-sm mb-2 card-libro";

                taskCard.innerHTML = `
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start">
                    <h6 class="mb-1">${objTask.titolo}</h6>
                    <span class="badge ${getColorePriorita(objTask.priorita)} font-monospace" style="font-size: 0.6rem;">
                        ${objTask.priorita}
                    </span>
                </div>
                <p class="small mb-1 text-secondary">${objTask.testo}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="fa-regular fa-calendar"></i> ${objTask.scadenza}</small>
                    <button class="btn btn-sm text-danger p-0" onclick="eliminaTask('${objTask.titolo}', this)">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>`;

                //il this nel button serve perchè poi il bottone.closest('.card') sa che deve partire da questo (this). e risalirà fino a trovare il genitore 
                // con quella classe specifica


                contenitoreTask.appendChild(taskCard);
            }
        }

        function eliminaTask(titoloDaCancellare, bottone) {
            if (confirm(`Vuoi davvero eliminare il task "${titoloDaCancellare}"?`)) {
                // 1. RIMOZIONE DALL'ARRAY (LOGICA)
                // Creiamo una nuova versione della lavagna ESCLUDENDO il task con quel titolo
                lavagna = lavagna.filter(task => task.titolo !== titoloDaCancellare);

                // 2. AGGIORNAMENTO STORAGE
                // Sovrascriviamo il vecchio database con quello nuovo
                localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));

                // 3. RIMOZIONE DAL DOM (UI)
                // Troviamo la card che contiene il bottone cliccato e la eliminiamo
                // bottone.closest() Individua fisicamente la card intera che contiene quel specifico tasto elimina.
                const cardTask = bottone.closest('.card');
                cardTask.remove();

                //console.log("Task rimosso:", titoloDaCancellare);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Carichiamo i nomi delle liste
            const listeSalvate = localStorage.getItem("nomiListeSalvate");

            if (listeSalvate) {
                nomiListe = JSON.parse(listeSalvate);
                // Per ogni nome salvato, dobbiamo ricreare la card e popolare il select
                nomiListe.forEach(titolo => {
                    stampaSingolaLista(titolo);
                });
                aggiornaSelectListe();
            }


            // --- CARICAMENTO TASK ---
            const taskSalvati = localStorage.getItem(STORAGE_KEY);
            if (taskSalvati) {
                lavagna = JSON.parse(taskSalvati);
                // Usiamo la nostra nuova funzione per ogni task nell'array
                lavagna.forEach(task => stampaSingoloTask(task));
            }




            // Gestione del form Task
            const taskForm = document.getElementById("book-form");


            taskForm.addEventListener("submit", function (e) {
                e.preventDefault();

                // 1. Creiamo l'oggetto con i valori attuali del form
                const objTask = {
                    titolo: document.getElementById("task-title").value,
                    testo: document.getElementById("task").value,
                    scadenza: document.getElementById("date-max").value,
                    priorita: document.getElementById("priorita").value,
                    listaRiferimento: document.getElementById("lista-riferimento").value // Il nome della lista a cui appartiene
                };

                // 2. Controllo sicurezza: l'utente ha scelto una lista?
                if (objTask.listaRiferimento === "") {
                    alert("Per favore, seleziona una lista a cui assegnare il task!");
                    return;
                }


                // 1. Aggiorniamo i Dati
                lavagna.push(objTask);

                // 2. Aggiorniamo lo Storage
                localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));

                // 3. Aggiorniamo la UI (chiamando la funzione creata sopra)
                stampaSingoloTask(objTask);

                taskForm.reset();
            });



            const clearBtn = document.getElementById("clear-all");
            clearBtn.addEventListener("click", function () {
                if (confirm("Sei sicuro di voler svuotare tutta la lavagna? Perderai tutte le liste e i task.")) {
                    // 1. Svuotiamo gli array (Dati)
                    lavagna = [];
                    nomiListe = [];

                    // 2. Cancelliamo tutto dal localStorage
                    localStorage.removeItem(STORAGE_KEY);
                    localStorage.removeItem("nomiListeSalvate");

                    // 3. Puliamo la UI
                    document.getElementById("container-liste").innerHTML = "";
                    aggiornaSelectListe(); // Svuota anche il menu a tendina

                    // 4. Feedback all'utente
                    //  alert("Lavagna svuotata!");

                    // Messaggio di successo
                    document.getElementById("container-liste").innerHTML = `
                <div class="col">
                    <div class="card shadow-sm border-start border-black border-1">
                        <div class="card-body py-2">
                            <h6 class="mb-0 text-success text-center">Lavagna svuotata con successo!</h6>
                        </div>
                    </div>
                </div>`;


                }
            });


        });




    </script>




</body>

</html>
