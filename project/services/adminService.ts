import { authService } from './authService';

/**
 * Service d'administration
 * Gère les fonctionnalités réservées aux administrateurs
 */
class AdminService {
  private baseUrl = 'http://localhost/student-payment-api';

  /**
   * Récupère les statistiques du dashboard
   */
  async getDashboardStats() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/admin/dashboard-stats.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.stats : {
        totalStudents: 0,
        totalPayments: 0,
        pendingPayments: 0,
        totalAmount: 0,
        paidAmount: 0,
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des statistiques:', error);
      return {
        totalStudents: 0,
        totalPayments: 0,
        pendingPayments: 0,
        totalAmount: 0,
        paidAmount: 0,
      };
    }
  }

  /**
   * Récupère tous les étudiants
   */
  async getAllStudents() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/admin/students.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.students : [];
    } catch (error) {
      console.error('Erreur lors de la récupération des étudiants:', error);
      return [];
    }
  }

  /**
   * Récupère les paiements en attente de validation
   */
  async getPendingPayments() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/admin/pending-payments.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.payments : [];
    } catch (error) {
      console.error('Erreur lors de la récupération des paiements en attente:', error);
      return [];
    }
  }

  /**
   * Récupère les paiements validés
   */
  async getValidatedPayments() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/admin/validated-payments.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.payments : [];
    } catch (error) {
      console.error('Erreur lors de la récupération des paiements validés:', error);
      return [];
    }
  }

  /**
   * Valide ou rejette un paiement
   */
  async validatePayment(paymentId: number, action: 'approve' | 'reject') {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/admin/validate-payment.php`, {
        method: 'POST',
        headers,
        body: JSON.stringify({ payment_id: paymentId, action }),
      });

      return await response.json();
    } catch (error) {
      console.error('Erreur lors de la validation du paiement:', error);
      return { success: false, message: 'Erreur de validation' };
    }
  }
}

export const adminService = new AdminService();