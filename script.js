const STORAGE_KEY = "miaLavagna";
let nomiListe = JSON.parse(localStorage.getItem("nomiListeSalvate")) || [];
let lavagna = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

// Funzione per creare e stampare la nuova lista
function aggiungiLista() {
    const input = document.getElementById("list-title");
    const titolo = input.value.trim();

    if (titolo !== "") {
        nomiListe.push(titolo);
        localStorage.setItem("nomiListeSalvate", JSON.stringify(nomiListe));
        stampaSingolaLista(titolo);
        aggiornaSelectListe();
        input.value = "";
    } else {
        alert("Inserisci un nome per la lista!");
    }
}

function aggiornaSelectListe() {
    const select = document.getElementById("lista-riferimento");
    select.innerHTML = '<option value="">Scegli una lista...</option>';
    nomiListe.forEach(nome => {
        const option = document.createElement("option");
        option.value = nome;
        option.textContent = nome;
        select.appendChild(option);
    });
}

function stampaSingolaLista(titolo) {
    const container = document.getElementById("container-liste");
    const col = document.createElement("div");
    col.className = "col-12 col-lg-6 mb-4";
    col.id = `lista-${titolo.replace(/\s+/g, '')}`;
    col.innerHTML = `
        <div class="card shadow-sm border-top border-info border-3">
            <div class="card-body">
                <h5 class="card-title text-black">${titolo}</h5>
                <hr>
                <div class="contenitore-task">
                    <p class="small text-muted italic">Nessun task in questa lista.</p>
                </div>
            </div>
        </div>`;
    container.appendChild(col);
}

function getColorePriorita(priorita) {
    switch (priorita.toLowerCase()) {
        case 'urgente e importante': return 'bg-danger';
        case 'urgente ma non importante': return 'bg-warning text-dark';
        case 'importante ma non urgente': return 'bg-info text-dark';
        default: return 'bg-secondary';
    }
}

function stampaSingoloTask(objTask) {
    const idCercato = "lista-" + objTask.listaRiferimento.replace(/\s+/g, '');
    const cardLista = document.getElementById(idCercato);

    if (cardLista) {
        const contenitoreTask = cardLista.querySelector(".contenitore-task");
        const placeholder = contenitoreTask.querySelector("p.text-muted");
        if (placeholder) placeholder.remove();

        const taskCard = document.createElement("div");
        taskCard.className = "card border-light shadow-sm mb-2 card-libro fade-in";
        taskCard.innerHTML = `
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start">
                    <h6 class="mb-1">${objTask.titolo}</h6>
                    <span class="badge ${getColorePriorita(objTask.priorita)}" style="font-size: 0.6rem;">${objTask.priorita}</span>
                </div>
                <p class="small mb-1 text-secondary">${objTask.testo}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="fa-regular fa-calendar"></i> ${objTask.scadenza}</small>
                    <button class="btn btn-sm text-danger p-0" onclick="eliminaTask('${objTask.titolo}', this)">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>`;
        contenitoreTask.appendChild(taskCard);
    }
}

// GESTIONE FORM TASK (Invia a Database + Visuale)

document.getElementById("book-form").addEventListener("submit", function(e) {
    e.preventDefault();

    const objTask = {
        titolo: document.getElementById("task-title").value,
        testo: document.getElementById("task").value,
        scadenza: document.getElementById("date-max").value,
        priorita: document.getElementById("priorita").value,
        listaRiferimento: document.getElementById("lista-riferimento").value
    };

    if (objTask.listaRiferimento === "") {
        alert("Seleziona una lista!");
        return;
    }

    // Invio dati al PHP tramite fetch (AJAX)
    fetch('salva_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(objTask)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // MOSTRA IL BANNER: lo rendiamo visibile e non mettiamo il setTimeout
            const alertBox = document.getElementById("success-alert");
            alertBox.style.display = "block"; 

            // LOGICA VISUALE (LocalStorage + Stampa)
            lavagna.push(objTask);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
            stampaSingoloTask(objTask);
            
            // Reset del form per nuovi inserimenti
            this.reset();
        } else {
            alert("Errore nel salvataggio: " + data.message);
        }
    })
    .catch(error => console.error('Errore:', error));
});