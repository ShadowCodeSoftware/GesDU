import * as SecureStore from 'expo-secure-store';

/**
 * Service d'authentification
 * Gère la connexion des étudiants et administrateurs
 */
class AuthService {
  private baseUrl = 'http://localhost/student-payment-api'; // Adresse de l'API PHP

  /**
   * Connexion étudiant avec matricule et mot de passe
   */
  async loginStudent(matricule: string, password: string) {
    try {
      const response = await fetch(`${this.baseUrl}/auth/student-login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ matricule, password }),
      });

      const data = await response.json();

      if (data.success) {
        // Sauvegarde des informations de session
        await SecureStore.setItemAsync('user_type', 'student');
        await SecureStore.setItemAsync('user_id', data.student.id.toString());
        await SecureStore.setItemAsync('user_token', data.token);
      }

      return data;
    } catch (error) {
      console.error('Erreur de connexion étudiant:', error);
      return { success: false, message: 'Erreur de connexion' };
    }
  }

  /**
   * Connexion administrateur
   */
  async loginAdmin(username: string, password: string) {
    try {
      const response = await fetch(`${this.baseUrl}/auth/admin-login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password }),
      });

      const data = await response.json();

      if (data.success) {
        // Sauvegarde des informations de session admin
        await SecureStore.setItemAsync('user_type', 'admin');
        await SecureStore.setItemAsync('user_id', 'admin');
        await SecureStore.setItemAsync('user_token', data.token);
      }

      return data;
    } catch (error) {
      console.error('Erreur de connexion admin:', error);
      return { success: false, message: 'Erreur de connexion' };
    }
  }

  /**
   * Déconnexion
   */
  async logout() {
    try {
      await SecureStore.deleteItemAsync('user_type');
      await SecureStore.deleteItemAsync('user_id');
      await SecureStore.deleteItemAsync('user_token');
    } catch (error) {
      console.error('Erreur lors de la déconnexion:', error);
    }
  }

  /**
   * Vérification de l'état de connexion
   */
  async isLoggedIn() {
    try {
      const userType = await SecureStore.getItemAsync('user_type');
      const token = await SecureStore.getItemAsync('user_token');
      return userType && token;
    } catch (error) {
      return false;
    }
  }

  /**
   * Obtenir le token d'authentification
   */
  async getAuthToken() {
    try {
      return await SecureStore.getItemAsync('user_token');
    } catch (error) {
      return null;
    }
  }

  /**
   * Obtenir les en-têtes d'authentification pour les requêtes API
   */
  async getAuthHeaders() {
    const token = await this.getAuthToken();
    return {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    };
  }
}

export const authService = new AuthService();