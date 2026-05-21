// L'arredatore: si occupa solo di cosa vede l'utente
//ui.js sa solo come costruire il rettangolino rosso della conferma.
const TaskUI = {
  // Funzione di utilità interna (helper) per i colori
  getColorePriorita: (priorita) => {
    if (!priorita) return "background-color: #cfd8dc; color: #37474f;";
    const p = priorita.toLowerCase();
    if (p.includes("urgente") || p.includes("alta"))
      return "background-color: #e57373; color: white;";
    if (p.includes("importante") || p.includes("media"))
      return "background-color: #ffb74d; color: white;";
    return "background-color: #90a4ae; color: white;";
  },

  stampaSingoloTask: (objTask) => {
    const idCercato = "lista-" + objTask.lista_riferimento.replace(/\s+/g, "");
    const cardLista = document.getElementById(idCercato);

    if (cardLista) {
      const contenitoreTask = cardLista.querySelector(".contenitore-task");
      const placeholder = contenitoreTask.querySelector("p.text-muted");
      if (placeholder) placeholder.remove();

      // Preview testo
      const descrizionePreview = objTask.testo
        ? objTask.testo.length > 40
          ? objTask.testo.substring(0, 40) + "..."
          : objTask.testo
        : "<i>Nessuna descrizione</i>";

      // Traduzione badge
      let testoPrioritaMostrato = objTask.priorita;
      const pLow = objTask.priorita.toLowerCase();
      if (pLow.includes("urgente")) testoPrioritaMostrato = "Urgente";
      else if (pLow.includes("media")) testoPrioritaMostrato = "Media";
      else if (pLow.includes("bassa")) testoPrioritaMostrato = "Bassa";

      const taskCard = document.createElement("div");
      taskCard.className = "card card-libro border-0 shadow-sm mb-3 fade-in";
      taskCard.dataset.id = objTask.id; 
      const titoloSafe = objTask.titolo.replace(/'/g, "\\'");

      // Usiamo TaskUI.getColorePriorita per il colore
      taskCard.innerHTML = `
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0" style="color: #37474f;">${objTask.titolo}</h6>
                        <span class="badge rounded-pill" style="${TaskUI.getColorePriorita(objTask.priorita)} font-size: 0.65rem;">
                            ${testoPrioritaMostrato}
                        </span>
                    </div>
                    <p class="small text-muted mb-2" title="${objTask.testo || ""}">${descrizionePreview}</p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-secondary"><i class="bi bi-calendar3 me-1"></i> ${objTask.scadenza}</small>
                        <div class="btn-group">
                            <button class="btn btn-sm text-primary me-2" onclick="preparaModifica('${titoloSafe}')">
                                <i class="bi bi-pencil" style="color: #f9a825;"></i>
                            </button>
                            <button class="btn btn-sm text-danger" onclick="chiediConfermaEliminaTask('${objTask.id}', this)">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
      contenitoreTask.prepend(taskCard);
    }
  },

  mostraNotifica: (messaggio, tipo = "success") => {
    const container = document.getElementById("toast-container");
    if (!container) return;
    const toast = document.createElement("div");
    toast.className = `alert alert-${tipo} border-0 shadow-lg fade-in mb-2 d-flex align-items-center justify-content-between`;
    toast.style.minWidth = "300px";
    toast.innerHTML = `
            <div><i class="bi bi-stars me-2"></i> ${messaggio}</div>
            <button type="button" class="btn-close ms-2" style="font-size: 0.7rem;" onclick="this.parentElement.remove()"></button>
        `;
    container.appendChild(toast);
    setTimeout(() => {
      if (toast && toast.parentElement) {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 500);
      }
    }, 4000);
  },

  pulisciForm: (formId) => {
    document.getElementById(formId)?.reset();
  },

  pulisciLavagna: () => {
    document
      .querySelectorAll(".contenitore-task")
      .forEach((c) => (c.innerHTML = ""));
  },

  gestisciBannerOspite: (isLogged) => {
    const banner = document.getElementById("guest-banner");
    if (isLogged) banner?.classList.add("d-none");
    else banner?.classList.remove("d-none");
  },

  mostraBoxConferma: (id) => {
    const container = document.getElementById("toast-container");
    if (!container) return;

    const confirmBox = document.createElement("div");
    confirmBox.className = `alert alert-danger border-0 shadow-lg fade-in mb-2`;
    confirmBox.innerHTML = `
            <p class="mb-2 small">Eliminare questo obiettivo?</p>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-light" onclick="confermaEliminazione('${id}', this)">Sì</button>
                <button class="btn btn-sm btn-outline-light" onclick="this.parentElement.parentElement.remove()">No</button>
            </div>`;
    container.appendChild(confirmBox);
  },

  rimuoviCardDalDOM: (elementoDalBottone) => {
    // Risale dal bottone fino alla card e la rimuove
    elementoDalBottone.closest(".card-libro").remove();
  },

  mostraModalModifica: (task) => {
    const oggi = new Date().toISOString().split("T")[0];
    const overlay = document.createElement("div");
    overlay.className = "edit-overlay fade-in";
    overlay.id = "full-edit-modal";

    // Puliamo i valori della priorità per il confronto
    const p = task.priorita.toLowerCase();

    overlay.innerHTML = `
            <div class="edit-card-modal p-4 shadow-lg">
                <h4 class="fw-bold mb-4 text-primary-zen">Modifica Obiettivo</h4>
                <div class="mb-3">
                    <label class="small fw-bold">TITOLO</label>
                    <input type="text" id="edit-t" class="form-control form-control-zen" value="${task.titolo}">
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">DESCRIZIONE</label>
                    <textarea id="edit-d" class="form-control form-control-zen" rows="3">${task.testo}</textarea>
                </div>
                <div class="row mb-4">
                    <div class="col-6">
                        <label class="small fw-bold">SCADENZA</label>
                        <input type="date" id="edit-s" class="form-control form-control-zen" value="${task.scadenza}" min="${oggi}">
                    </div>
                    <div class="col-6">
                        <label class="small fw-bold">PRIORITÀ</label>
                        <select id="edit-p" class="form-select form-control-zen">
              <option value="urgente" ${task.priorita === "urgente" ? "selected" : ""}>Urgente</option>
    <option value="media" ${task.priorita === "media" ? "selected" : ""}>Media</option>
    <option value="bassa" ${task.priorita === "bassa" ? "selected" : ""}>Bassa</option>
</select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-zen-primary w-100" id="save-edit-btn">Salva</button>
                    <button class="btn btn-outline-secondary w-100" onclick="document.getElementById('full-edit-modal').remove()">Annulla</button>
                </div>
            </div>`;
    document.body.appendChild(overlay);
  },

  chiudiModalModifica: () => {
    document.getElementById("full-edit-modal")?.remove();
  },

  stampaSingolaLista: (titolo) => {
    const container = document.getElementById("container-liste");
    if (!container) return;

    const col = document.createElement("div");
    col.className = "col-12 col-lg-6 mb-4 fade-in";
    col.id = `lista-${titolo.replace(/\s+/g, "")}`;
    col.innerHTML = `
            <div class="card card-zen shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-primary-zen mb-0 editable-title">${titolo}</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm text-muted" onclick="TaskUI.attivaEditLista(this.closest('.card-body').querySelector('.editable-title'), '${titolo}')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm text-danger" onclick="chiediConfermaEliminaLista('${titolo}')">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <hr class="opacity-10">
                    <div class="contenitore-task"></div>
                </div>
            </div>`;
    container.appendChild(col);
  },

  attivaEditLista: (elemento, vecchioTitolo) => {
    const isLogged = localStorage.getItem("isLogged") === "true";

    // BLOCCO IMMEDIATO: Se non è loggato, non trasformiamo nemmeno il titolo in input
    if (!isLogged) {
      TaskUI.mostraNotifica("Accedi per rinominare le liste!", "info");
      return;
    }

    const input = document.createElement("input");
    input.type = "text";
    input.className = "input-edit-inline form-control";
    input.value = vecchioTitolo;
    elemento.replaceWith(input);
    input.focus();

    input.onblur = () => eseguiRinominaLista(input, vecchioTitolo);
  },

  mostraConfirmSvuota: () => {
    const container = document.getElementById("toast-container");
    const confirmBox = document.createElement("div");
    confirmBox.className = "alert alert-danger border-0 shadow-lg fade-in mb-2";
    confirmBox.style.borderLeft = "5px solid #d32f2f";
    confirmBox.innerHTML = `
            <div class="p-1">
                <p class="mb-2 small fw-bold text-dark">Attenzione: vuoi svuotare l'intera lavagna?</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-danger" id="confirm-clear-all">Svuota Tutto</button>
                    <button class="btn btn-sm btn-light" onclick="this.closest('.alert').remove()">Annulla</button>
                </div>
            </div>`;
    container.appendChild(confirmBox);
    return confirmBox; // Lo restituiamo per agganciare l'evento in app.js
  },

  aggiornaUIUtente: () => {
    const isLogged = localStorage.getItem("isLogged") === "true";
    const labelAccount = document.querySelector("#userMenu span");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    if (isLogged && labelAccount) {
      labelAccount.textContent = localStorage.getItem("userZen");
      dropdownMenu.innerHTML = `
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profilo</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Impostazioni</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="javascript:void(0)" id="btn-logout-dropdown"><i class="bi bi-box-arrow-right me-2"></i>Esci</a></li>
            `;
      // Agganciamo l'evento logout subito dopo aver creato il bottone
      document
        .getElementById("btn-logout-dropdown")
        ?.addEventListener("click", logout);
    }
  },

  aggiornaSelectListe: (nomiListe) => {
    const select = document.getElementById("lista-riferimento");
    if (!select) return;
    select.innerHTML = '<option value="">Scegli una lista...</option>';
    nomiListe.forEach((nome) => {
      const opt = document.createElement("option");
      opt.value = opt.textContent = nome;
      select.appendChild(opt);
    });
  },

mostraModalAuth: (modo) => {
    isLoginMode = (modo === "login");
    
    // Cambiamo il titolo e il testo del bottone
    const titolo = document.getElementById("authTitle");
    const submitBtn = document.getElementById("btn-auth-submit");
    if (titolo) titolo.textContent = isLoginMode ? "Bentornato" : "Crea Account";
    if (submitBtn) submitBtn.textContent = isLoginMode ? "Accedi" : "Registrati";

    // Gestiamo la visibilità dei gruppi (Nome, Conferma Password, Termini)
    // Usiamo .style.display invece di .classList così non rischiamo errori
    const groupNome = document.getElementById("group-nome");
    const groupConfirm = document.getElementById("confirm-password-group");
    const groupTerms = document.getElementById("terms-group");

    const displayStyle = isLoginMode ? "none" : "block";

    if (groupNome) groupNome.style.display = displayStyle;
    if (groupConfirm) groupConfirm.style.display = displayStyle;
    if (groupTerms) groupTerms.style.display = displayStyle;

    // Mostriamo la modale
    const modalElement = document.getElementById("authModal");
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        modal.show();
    }
    return isLoginMode;
},

  // --- NUOVO METODO TaskUI.mostraConfirmEliminaLista ---
  mostraConfirmEliminaLista: (titolo) => {
    const container = document.getElementById("toast-container");
    const box = document.createElement("div");
    box.className = "alert alert-danger border-0 shadow-lg fade-in mb-2";
    box.innerHTML = `
        <p class="mb-2 small fw-bold">Eliminare la lista "${titolo}" e i suoi task?</p>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-danger" id="confirm-delete-list">Elimina</button>
            <button class="btn btn-sm btn-light" onclick="this.closest('.alert').remove()">Annulla</button>
        </div>`;
    container.appendChild(box);
    return box;
  },
};
