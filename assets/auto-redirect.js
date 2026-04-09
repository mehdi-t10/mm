/**
 * Auto-Redirect Script
 * Redirige automatiquement les utilisateurs vers le bon dashboard s'ils sont connectés
 * À inclure dans index.html, client-dashboard.html, et admin-dashboard.html
 */

(function() {
  // Vérifier si SessionManager est disponible
  if (typeof sessionManager === 'undefined') {
    return;
  }

  // Attendre que le DOM soit prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkAndRedirect);
  } else {
    checkAndRedirect();
  }

  function checkAndRedirect() {
    const currentPage = getCurrentPageType();
    const sessionType = sessionManager.getSessionType();

    // Vérifier si nous venons d'une redirection de connexion (données en URL)
    const urlParams = new URLSearchParams(window.location.search);
    const userFromUrl = urlParams.get('user');
    const comingFromLogin = userFromUrl ? true : false;

    // Si l'utilisateur est sur la page de login (index.html)
    if (currentPage === 'login') {
      if (sessionManager.isAdminLoggedIn()) {
        window.location.href = 'admin-dashboard.html';
      } else if (sessionManager.isUserLoggedIn()) {
        window.location.href = 'client-dashboard.html';
      }
    }
    // Si l'utilisateur est sur le dashboard client
    else if (currentPage === 'client-dashboard') {
      if (sessionManager.isAdminLoggedIn()) {
        window.location.href = 'admin-dashboard.html';
      } else if (!sessionManager.isUserLoggedIn() && !comingFromLogin) {
        // Ne rediriger que si pas connecté ET pas en train de se connecter
        window.location.href = 'index.html';
      } else {
        // L'utilisateur est bien connecté (ou vient de se connecter), initialiser le dashboard
        if (sessionManager.isUserLoggedIn() || comingFromLogin) {
          initClientDashboard();
        }
      }
    }
    // Si l'utilisateur est sur le dashboard admin
    else if (currentPage === 'admin-dashboard') {
      if (sessionManager.isUserLoggedIn() && !sessionManager.isAdminLoggedIn()) {
        window.location.href = 'client-dashboard.html';
      } else if (!sessionManager.isAdminLoggedIn()) {
        window.location.href = 'index.html';
      } else {
        // L'admin est bien connecté, initialiser le dashboard
        initAdminDashboard();
      }
    }
  }

  /**
   * Détermine la page actuelle
   * @returns {string} 'login', 'client-dashboard', 'admin-dashboard', ou 'other'
   */
  function getCurrentPageType() {
    const path = window.location.pathname.toLowerCase();
    const filename = path.split('/').pop();

    if (filename === 'index.html' || filename === '') {
      return 'login';
    } else if (filename === 'client-dashboard.html') {
      return 'client-dashboard';
    } else if (filename === 'admin-dashboard.html') {
      return 'admin-dashboard';
    }
    return 'other';
  }

  /**
   * Initialise le dashboard client avec les données de session
   */
  function initClientDashboard() {
    const user = sessionManager.getCurrentUser();

    // Afficher les informations utilisateur dans l'interface
    const elements = {
      userEmail: document.querySelectorAll('[data-user-email]'),
      userName: document.querySelectorAll('[data-user-name]'),
      userInfo: document.querySelector('#userInfo')
    };

    if (user) {
      elements.userEmail.forEach(el => {
        el.textContent = user.email;
      });

      const fullName = `${user.prenom || ''} ${user.nom || ''}`.trim();
      elements.userName.forEach(el => {
        el.textContent = fullName || user.email;
      });

      if (elements.userInfo) {
        elements.userInfo.innerHTML = `
          <div style="padding: 12px; background: rgba(0,230,118,0.1); border: 1px solid var(--green); border-radius: 8px;">
            <div><strong>[USER] ${fullName}</strong></div>
            <div style="font-size: 0.85rem; color: var(--muted);">[EMAIL] ${user.email}</div>
          </div>
        `;
      }
    }
  }

  /**
   * Initialise le dashboard admin avec les données de session
   */
  function initAdminDashboard() {
    const admin = sessionManager.getCurrentAdmin();

    // Afficher les informations admin dans l'interface
    const elements = {
      adminEmail: document.querySelectorAll('[data-admin-email]'),
      adminName: document.querySelectorAll('[data-admin-name]'),
      adminInfo: document.querySelector('#adminInfo')
    };

    if (admin) {
      elements.adminEmail.forEach(el => {
        el.textContent = admin.email;
      });

      const fullName = `${admin.prenom || ''} ${admin.nom || ''}`.trim();
      elements.adminName.forEach(el => {
        el.textContent = fullName || admin.email;
      });

      if (elements.adminInfo) {
        elements.adminInfo.innerHTML = `
          <div style="padding: 12px; background: rgba(255,215,64,0.1); border: 1px solid var(--gold); border-radius: 8px;">
            <div><strong>[SECURE] Admin: ${fullName}</strong></div>
            <div style="font-size: 0.85rem; color: var(--muted);">[EMAIL] ${admin.email}</div>
          </div>
        `;
      }
    }
  }

  /**
   * Expose une fonction de logout globale
   */
  window.logout = function() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter?')) {
      sessionManager.logoutAll();
      window.location.href = 'index.html';
    }
  };
})();
