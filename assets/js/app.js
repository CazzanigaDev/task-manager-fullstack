// file generale app.js decide: "Se l'utente è loggato, prima chiedi al postino di cancellare nel DB, poi aggiorna la memoria locale e avvisa l'arredatore di pulire".

// Variabili di Stato Globali
const STORAGE_KEY = "miaLavagna";
const LISTE_KEY = "nomiListeSalvate";

// Recuperiamo subito i dati locali (fondamentale per gli ospiti)
let nomiListe = JSON.parse(localStorage.getItem(LISTE_KEY)) || ["Da smistare"];
let lavagna = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
let isLoginMode = true;

// Definizione delle funzioni

// Funzione per aprire la modale (chiamata dai tasti Login/Signup nella navbar)
const apriAuth = (modo) => {
  isLoginMode = TaskUI.mostraModalAuth(modo);
};

// Logica di Logout -- aggiornata
const logout = async () => {
  try {
    // Chiediamo al server di distruggere la sessione
    const res = await fetch("api/logout.php");
    const data = await res.json();

    if (data.status === "success") {
      // Puliamo i dati di login ma TENIAMO i task locali se vogliamo che l'ospite li veda
      localStorage.removeItem("isLogged");
      localStorage.removeItem("userZen");

      TaskUI.mostraNotifica("Sessione terminata", "info");
      setTimeout(() => (location.href = "index.php"), 1000); // Ricarica pulito
    }
  } catch (e) {
    // Se il server fallisce, puliamo comunque il locale per sicurezza
    localStorage.clear();
    location.reload();
  }
};

// Funzione ponte per aggiornare le select
const aggiornaSelectListe = () => {
  TaskUI.aggiornaSelectListe(nomiListe);
};

// Funzione ponte per l'UI utente
const aggiornaUIUtente = () => {
  TaskUI.aggiornaUIUtente();
};

// --- FUNZIONI DI GESTIONE LISTE ---

// --- LOGICA RINOMINA LISTA AGGIORNATA ---
const eseguiRinominaLista = (input, vecchioTitolo) => {
  const isLogged = localStorage.getItem("isLogged") === "true";

  // Se non è loggato, impediamo la rinomina (o gestiscila solo locale se preferisci)
  if (!isLogged) {
    TaskUI.mostraNotifica(
      "Registrati per gestire le liste personalizzate!",
      "info",
    );
    apriAuth("signup");
    location.reload(); // Ripristina il titolo originale
    return;
  }

  const nuovo = input.value.trim();
  if (!nuovo || vecchioTitolo === nuovo) return location.reload();

  const index = nomiListe.indexOf(vecchioTitolo);
  if (index !== -1) {
    nomiListe[index] = nuovo;
    localStorage.setItem(LISTE_KEY, JSON.stringify(nomiListe));
    lavagna.forEach((t) => {
      if (t.lista_riferimento === vecchioTitolo) t.lista_riferimento = nuovo;
    });
    localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
    // Qui poi aggiungeremo la chiamata API per rinominare sul DB
    location.reload();
  }
};

//aggiungiLista
const aggiungiLista = () => {
  const input = document.getElementById("list-title");
  const titolo = input?.value.trim();

  if (
    !titolo ||
    nomiListe.some((l) => l.toLowerCase() === titolo.toLowerCase())
  ) {
    input?.classList.add("is-invalid");
    return;
  }

  input.classList.remove("is-invalid");
  nomiListe.push(titolo);
  localStorage.setItem(LISTE_KEY, JSON.stringify(nomiListe));
  TaskUI.stampaSingolaLista(titolo);
  if (typeof aggiornaSelectListe === "function") aggiornaSelectListe();
  input.value = "";
};

//chiediConfermaEliminaLista
const chiediConfermaEliminaLista = (titolo) => {
  const isLogged = localStorage.getItem("isLogged") === "true";

  // Se non è loggato, mostriamo il banner di avviso invece della conferma
  if (!isLogged) {
    TaskUI.mostraNotifica("Accedi per personalizzare le tue liste!", "info");
    return; // Ci fermiamo qui
  }

  // Se è loggato, procediamo col banner di conferma eliminazione
  const box = TaskUI.mostraConfirmEliminaLista(titolo);
  box.querySelector("#confirm-delete-list").onclick = () => {
    nomiListe = nomiListe.filter((l) => l !== titolo);
    lavagna = lavagna.filter((t) => t.lista_riferimento !== titolo);
    localStorage.setItem(LISTE_KEY, JSON.stringify(nomiListe));
    localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
    location.reload();
  };
};
const svuotaLavagna = () => {
  // Rimuoviamo eventuali banner già aperti prima di crearne uno nuovo
  document.querySelector(".alert-danger")?.remove();

  const box = TaskUI.mostraConfirmSvuota();
  box.querySelector("#confirm-clear-all").onclick = () => {
    localStorage.clear(); // Pulisce tutto
    location.reload();
  };
};

// --- FUNZIONI DI GESTIONE TASK ---
//caricaDati
const caricaDati = async () => {
  // 1. Chiediamo i dati al postino (api.js)
  const data = await TaskAPI.getAll();

  if (data.status === "success") {
    // UTENTE LOGGATO
    lavagna = data.data; // Assicurati che il PHP restituisca i task in 'data' o 'tasks'
    localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
    TaskUI.gestisciBannerOspite(true); // Usiamo una funzione di ui.js
  } else {
    // UTENTE OSPITE o OFFLINE
    lavagna = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    TaskUI.gestisciBannerOspite(false);
  }

  // 2. Usiamo l'arredatore per pulire e stampare (ui.js)
  TaskUI.pulisciLavagna();
  lavagna.forEach((t) => TaskUI.stampaSingoloTask(t));
};

//preparaModifica
const preparaModifica = (titoloTask) => {
  // Troviamo il task specifico tramite il titolo (o meglio ancora tramite ID se lo hai)
  const taskIndex = lavagna.findIndex((t) => t.titolo === titoloTask);
  if (taskIndex === -1) return;

  const task = lavagna[taskIndex];
  TaskUI.mostraModalModifica(task);

  document.getElementById("save-edit-btn").onclick = async () => {
    const isLogged = localStorage.getItem("isLogged") === "true";

    // Creiamo un nuovo oggetto con i dati aggiornati
    const taskAggiornato = {
      ...task, // mantiene l'ID originale e la lista di riferimento
      titolo: document.getElementById("edit-t").value.trim(),
      testo: document.getElementById("edit-d").value.trim(),
      scadenza: document.getElementById("edit-s").value,
      priorita: document.getElementById("edit-p").value,
    };

    if (isLogged) {
      const data = await TaskAPI.update(taskAggiornato);
      if (data.status !== "success") {
        return TaskUI.mostraNotifica("Errore aggiornamento server", "danger");
      }
    }

    // AGGIORNAMENTO LOCALE: Sostituiamo il vecchio col nuovo nella stessa posizione
    lavagna[taskIndex] = taskAggiornato;

    localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
    TaskUI.mostraNotifica("Obiettivo aggiornato!", "success");
    TaskUI.chiudiModalModifica();

    // Ricarichiamo per aggiornare la vista
    setTimeout(() => location.reload(), 500);
  };
};

// --- CONFERMA ELIMINA TASK ---
// Questa viene chiamata dal bottone "Cestino" della card
const chiediConfermaEliminaTask = (id) => {
  TaskUI.mostraBoxConferma(id);
};

// Questa viene chiamata dal tasto "Sì" nel box di conferma
const confermaEliminazione = async (id, btnSì) => {
  const isLogged = localStorage.getItem("isLogged") === "true";
  // Se loggato, prova a cancellare dal DB
  if (isLogged) {
    try {
      const data = await TaskAPI.delete(id);
      if (data.status !== "success") {
        return TaskUI.mostraNotifica(
          "Errore server: " + data.message,
          "danger",
        );
      }
    } catch (e) {
      console.error("Errore server durante eliminazione");
    }
  }

  // Per TUTTI (Ospiti e Loggati): rimozione locale
  lavagna = lavagna.filter((t) => t.id != id);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));

  // Rimuove il box di conferma
  btnSì.closest(".alert").remove();

  // Rimuove la card dal DOM
  const taskCard = document.querySelector(`[data-id="${id}"]`);
  if (taskCard) {
    const contenitore = taskCard.closest(".contenitore-task");
    taskCard.remove();

    // Se il contenitore è rimasto vuoto, rimuovi anche lui
    if (contenitore && contenitore.children.length === 0) {
      contenitore.remove();
    }
  }

  TaskUI.mostraNotifica("Obiettivo rimosso", "warning");
};

const resetFormTask = () => {
  document.getElementById("book-form").reset();
  const oggi = new Date().toISOString().split("T")[0];
  document.getElementById("date-max").value = oggi;
};

// --- FIX INIT & EVENTI (Unisci tutto qui e cancella i duplicati in fondo) ---
document.addEventListener("DOMContentLoaded", () => {
  // Inizializzazione date
  const dateInput = document.getElementById("date-max");
  if (dateInput) {
    const oggi = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", oggi);
    dateInput.value = oggi;
  }

  // Garanzia lista base
  if (!nomiListe.includes("Da smistare")) {
    nomiListe.unshift("Da smistare");
  }

  // UI Iniziale
  //  TaskUI.pulisciLavagna(); ripetizione
  nomiListe.forEach((titolo) => TaskUI.stampaSingolaLista(titolo));
  TaskUI.aggiornaSelectListe(nomiListe);
  TaskUI.aggiornaUIUtente();
  caricaDati();

  // --- UNICO PUNTO DI GESTIONE EVENTI ---
  document
    .getElementById("btn-add-list")
    ?.addEventListener("click", aggiungiLista);
  document
    .getElementById("btn-empty-all")
    ?.addEventListener("click", svuotaLavagna);

  // --- GESTORE APERTURA MODALE ---
  document.getElementById("nav-login-btn")?.addEventListener("click", (e) => {
    e.preventDefault();
    apriAuth("login");
  });

  document.getElementById("nav-signup-btn")?.addEventListener("click", (e) => {
    e.preventDefault();
    apriAuth("signup");
  });

  // --- GESTORE SWITCH LOGIN/REGISTRAZIONE (Dentro la modale) ---
  document
    .getElementById("toggleAuthMode")
    ?.addEventListener("click", function (e) {
      e.preventDefault();
      // Invertiamo la modalità
      isLoginMode = !isLoginMode;
      // Chiamiamo la funzione UI per aggiornare i campi visibili
      TaskUI.mostraModalAuth(isLoginMode ? "login" : "signup");

      // Cambiamo il testo del link
      this.textContent = isLoginMode
        ? "Non hai un account? Registrati"
        : "Hai già un account? Accedi";
    });

  // --- GESTORE INVIO FORM AUTH (Login/Signup) ---
  document
    .getElementById("auth-form")
    ?.addEventListener("submit", async function (e) {
      e.preventDefault(); // Blocca il ricaricamento della pagina
      const isSignup = !isLoginMode;

      // 2. Prepariamo il pacchetto da inviare al server
      const payload = {
        azione: isLoginMode ? "login" : "signup",
        email: document.getElementById("auth-email").value,
        password: document.getElementById("auth-password").value,
      };

      if (isSignup) {
        const nome = document.getElementById("auth-nome").value;
        const confirm = document.getElementById("auth-password-confirm").value;

        if (payload.password !== confirm) {
          return TaskUI.mostraNotifica("Le password non coincidono", "danger");
        }
        payload.nome = nome;
        payload.termini =
          document.getElementById("auth-terms")?.checked ?? false;
        payload.captchaResponse = grecaptcha.getResponse();
      }

      // 3. Chiediamo al "postino" (api.js) di parlare con il PHP
      const data = await TaskAPI.auth(payload);

      if (data.status === "success") {
        // Se va bene, salviamo chi è l'utente e ricarichiamo
        localStorage.setItem("userZen", data.user);
        localStorage.setItem("isLogged", "true");

        TaskUI.mostraNotifica(
          isLoginMode ? "Bentornato!" : "Account creato!",
          "success",
        );

        setTimeout(() => location.reload(), 1000);
      } else {
        // Se c'è un errore (es. password errata), lo mostriamo nel banner
        TaskUI.mostraNotifica(data.message, "danger");
      }
    });

  // --- Gestione INVIO TASK ---
  document
    .getElementById("book-form")
    ?.addEventListener("submit", async function (e) {
      e.preventDefault();
      const isLogged = localStorage.getItem("isLogged") === "true";

      const objTask = {
        id: isLogged ? null : Date.now(),
        titolo: document.getElementById("task-title").value.trim(),
        testo: document.getElementById("task").value.trim(),
        scadenza: document.getElementById("date-max").value,
        priorita: document.getElementById("priorita").value,
        lista_riferimento:
          document.getElementById("lista-riferimento").value || "Da smistare",
      };

      if (isLogged) {
        const data = await TaskAPI.save(objTask); // Usa l'oggetto TaskAPI di api.js
        if (data.status === "success") {
          objTask.id = data.id;
          TaskUI.stampaSingoloTask(objTask);
          lavagna.push(objTask);
          localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
          resetFormTask();
          TaskUI.mostraNotifica("Task salvato!", "success");
        }
      } else {
        // Logica Ospite
        lavagna.push(objTask);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(lavagna));
        TaskUI.stampaSingoloTask(objTask);
        resetFormTask();
        TaskUI.mostraNotifica("Salvato in locale", "info");
      }
    });
});
