/**
 * Session Manager - Gère les sessions persistantes
 * Permet aux utilisateurs de rester connectés entre les onglets/navigations
 */

class SessionManager {
  constructor() {
    this.STORAGE_KEYS = {
      currentUser: 'currentUser',
      currentAdmin: 'currentAdmin',
      lastLogin: 'lastLogin',
      sessionToken: 'sessionToken'
    };
  }

  /**
   * Vérifie et récupère l'utilisateur connecté
   * @returns {Object|null} Les données de l'utilisateur ou null
   */
  getCurrentUser() {
    try {
      const user = localStorage.getItem(this.STORAGE_KEYS.currentUser);
      return user ? JSON.parse(user) : null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Vérifie et récupère l'admin connecté
   * @returns {Object|null} Les données de l'admin ou null
   */
  getCurrentAdmin() {
    try {
      const admin = localStorage.getItem(this.STORAGE_KEYS.currentAdmin);
      return admin ? JSON.parse(admin) : null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Retourne le type de session actuelle
   * @returns {string} 'client', 'admin', ou 'none'
   */
  getSessionType() {
    if (this.getCurrentAdmin()) return 'admin';
    if (this.getCurrentUser()) return 'client';
    return 'none';
  }

  /**
   * Vérifie si l'utilisateur est connecté
   * @returns {boolean}
   */
  isUserLoggedIn() {
    return this.getCurrentUser() !== null;
  }

  /**
   * Vérifie si l'admin est connecté
   * @returns {boolean}
   */
  isAdminLoggedIn() {
    return this.getCurrentAdmin() !== null;
  }

  /**
   * Sauvegarde les données d'un utilisateur client
   * @param {Object} userData Les données de l'utilisateur
   */
   saveUserSession(userData) {
     try {
       localStorage.setItem(this.STORAGE_KEYS.currentUser, JSON.stringify(userData));
       localStorage.setItem(this.STORAGE_KEYS.lastLogin, new Date().toISOString());
       sessionStorage.setItem(this.STORAGE_KEYS.currentUser, JSON.stringify(userData));
     } catch (e) {
     }
   }

   /**
    * Sauvegarde les données d'un admin
    * @param {Object} adminData Les données de l'admin
    */
   saveAdminSession(adminData) {
     try {
       localStorage.setItem(this.STORAGE_KEYS.currentAdmin, JSON.stringify(adminData));
       localStorage.setItem(this.STORAGE_KEYS.lastLogin, new Date().toISOString());
       sessionStorage.setItem(this.STORAGE_KEYS.currentAdmin, JSON.stringify(adminData));
     } catch (e) {
     }
   }

   /**
    * Déconnecte l'utilisateur client
    */
   logoutUser() {
     try {
       localStorage.removeItem(this.STORAGE_KEYS.currentUser);
       sessionStorage.removeItem(this.STORAGE_KEYS.currentUser);
     } catch (e) {
     }
   }

   /**
    * Déconnecte l'admin
    */
   logoutAdmin() {
     try {
       localStorage.removeItem(this.STORAGE_KEYS.currentAdmin);
       sessionStorage.removeItem(this.STORAGE_KEYS.currentAdmin);
     } catch (e) {
     }
   }

  /**
   * Déconnecte complètement l'utilisateur
   */
  logoutAll() {
    this.logoutUser();
    this.logoutAdmin();
    localStorage.removeItem(this.STORAGE_KEYS.lastLogin);
    localStorage.removeItem(this.STORAGE_KEYS.sessionToken);
  }

  /**
   * Affiche les informations de débogage
   */
  debug() {
  }
}

// Exporter le gestionnaire de session
const sessionManager = new SessionManager();

