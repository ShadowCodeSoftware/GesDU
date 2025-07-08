import { authService } from './authService';

/**
 * Service de gestion des paiements
 * Gère les transactions et validations
 */
class PaymentService {
  private baseUrl = 'http://localhost/student-payment-api';

  /**
   * Effectue un paiement
   */
  async makePayment(paymentData: any) {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/payment/make-payment.php`, {
        method: 'POST',
        headers,
        body: JSON.stringify(paymentData),
      });

      return await response.json();
    } catch (error) {
      console.error('Erreur lors du paiement:', error);
      return { success: false, message: 'Erreur de paiement' };
    }
  }

  /**
   * Récupère l'historique des paiements d'un étudiant
   */
  async getPaymentHistory() {
    try {
      const headers = await authService.getAuthHeaders();
      const response = await fetch(`${this.baseUrl}/payment/history.php`, {
        method: 'GET',
        headers,
      });

      const data = await response.json();
      return data.success ? data.payments : [];
    } catch (error) {
      console.error('Erreur lors de la récupération de l\'historique:', error);
      return [];
    }
  }
}

export const paymentService = new PaymentService();