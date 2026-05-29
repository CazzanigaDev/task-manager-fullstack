//Qui metti solo le chiamate al server. Così se domani cambi il nome di un file PHP, lo cambi solo qui.
//api.js sa solo che deve mandare un ID a un file PHP.

const TaskAPI = {
  // Caricamento: restituisce solo i dati grezzi dal server
  getAll: async () => {
    try {
      const res = await fetch("api/get_tasks.php");
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },
  // Eliminazione: invia l'ID e restituisce la conferma
  delete: async (id) => {
    try {
      const res = await fetch("api/delete_task.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id }),
      });
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },
  save: async (objTask) => {
    const res = await fetch("api/save_task.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(objTask),
    });
    return await res.json();
  },

  auth: async (payload) => {
    const res = await fetch("api/auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    return await res.json();
  },

  update: async (task) => {
    try {
      const res = await fetch("api/save_task.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(task), // Qui passiamo tutto l'oggetto con l'ID
      });
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },

  // --- NUOVE CHIAMATE PER LA GESTIONE DELLE LISTE ---
  getLists: async () => {
    try {
      const res = await fetch("api/get_lists.php");
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },

  saveList: async (payload) => {
    try {
      const res = await fetch("api/save_lists.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload), // payload può contenere { titolo } per creare o { id, titolo } per rinominare
      });
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },

  deleteList: async (payload) => {
    try {
      const res = await fetch("api/delete_lists.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload), // payload sarà { titolo: "Nome Lista" }
      });
      return await res.json();
    } catch (e) {
      return { status: "error", message: "Errore di connessione" };
    }
  },
};
