import { authService } from './authService';

/**
 * Service pour les fonctionnalités étudiants
 * Gère les données personnelles et le statut de paiement
 */
class StudentService {
  private baseUrl = 'http://localhost/student-payment-api';

  /**
   * Récupère les informations de l'étudiant connecté
   */
  async getCurrentStudent() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/student/profile.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.student : null;
    } catch (error) {
      console.error('Erreur lors de la récupération du profil:', error);
      return null;
    }
  }

  /**
   * Récupère le statut de paiement de l'étudiant
   */
  async getPaymentStatus() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/student/payment-status.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.status : { tranche1: false, tranche2: false };
    } catch (error) {
      console.error('Erreur lors de la récupération du statut de paiement:', error);
      return { tranche1: false, tranche2: false };
    }
  }

  /**
   * Met à jour les informations de l'étudiant
   */
  async updateProfile(profileData: any) {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/student/update-profile.php`, {
        method: 'POST',
        headers,
        body: JSON.stringify(profileData),
      });

      return await response.json();
    } catch (error) {
      console.error('Erreur lors de la mise à jour du profil:', error);
      return { success: false, message: 'Erreur de mise à jour' };
    }
  }
}

export const studentService = new StudentService();