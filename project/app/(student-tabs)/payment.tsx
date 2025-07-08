import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert } from 'react-native';
import { CreditCard, CheckCircle, Clock, DollarSign } from 'lucide-react-native';
import { paymentService } from '@/services/paymentService';
import { studentService } from '@/services/studentService';

/**
 * Page de paiement pour les étudiants
 * Permet d'effectuer les paiements des deux tranches
 */
export default function PaymentPage() {
  const [studentData, setStudentData] = useState(null);
  const [paymentStatus, setPaymentStatus] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);

  const loadData = async () => {
    try {
      const [student, payments] = await Promise.all([
        studentService.getCurrentStudent(),
        studentService.getPaymentStatus()
      ]);
      setStudentData(student);
      setPaymentStatus(payments);
    } catch (error) {
      console.error('Erreur lors du chargement:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handlePayment = async (tranche) => {
    Alert.alert(
      'Confirmer le paiement',
      `Voulez-vous payer la ${tranche === 1 ? 'première' : 'deuxième'} tranche de 25,000 FCFA ?`,
      [
        { text: 'Annuler', style: 'cancel' },
        { 
          text: 'Confirmer', 
          onPress: () => processPayment(tranche),
          style: 'default'
        }
      ]
    );
  };

  const processPayment = async (tranche) => {
    setProcessing(true);
    try {
      const paymentData = {
        matricule: studentData.matricule,
        montant: 25000,
        tranche: tranche,
        filiere: studentData.filiere,
        faculte: studentData.departement,
        universite: 'Université Centrale',
        annee_academique: studentData.annee_academique
      };

      const result = await paymentService.makePayment(paymentData);
      
      if (result.success) {
        Alert.alert(
          'Paiement Effectué',
          `Votre paiement de la tranche ${tranche} a été envoyé pour validation.`,
          [{ text: 'OK', onPress: loadData }]
        );
      } else {
        Alert.alert('Erreur', result.message);
      }
    } catch (error) {
      Alert.alert('Erreur', 'Erreur lors du paiement. Veuillez réessayer.');
    } finally {
      setProcessing(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {/* Informations de paiement */}
      <View style={styles.infoCard}>
        <Text style={styles.cardTitle}>Informations de Paiement</Text>
        
        <View style={styles.infoRow}>
          <Text style={styles.label}>Matricule:</Text>
          <Text style={styles.value}>{studentData?.matricule}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.label}>Filière:</Text>
          <Text style={styles.value}>{studentData?.filiere}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.label}>Faculté:</Text>
          <Text style={styles.value}>{studentData?.departement}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.label}>Université:</Text>
          <Text style={styles.value}>Université Centrale</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.label}>Année Académique:</Text>
          <Text style={styles.value}>{studentData?.annee_academique}</Text>
        </View>
      </View>

      {/* Première tranche */}
      <View style={styles.paymentCard}>
        <View style={styles.paymentHeader}>
          <View>
            <Text style={styles.trancheTitle}>Première Tranche</Text>
            <Text style={styles.amount}>25,000 FCFA</Text>
          </View>
          {paymentStatus?.tranche1 ? (
            <CheckCircle size={32} color="#10B981" />
          ) : (
            <Clock size={32} color="#F59E0B" />
          )}
        </View>
        
        <Text style={styles.description}>
          Paiement des frais universitaires - Première partie
        </Text>
        
        {!paymentStatus?.tranche1 ? (
          <TouchableOpacity 
            style={styles.payButton}
            onPress={() => handlePayment(1)}
            disabled={processing}
          >
            <CreditCard size={20} color="white" />
            <Text style={styles.payButtonText}>
              {processing ? 'Traitement...' : 'Payer Maintenant'}
            </Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.paidButton}>
            <CheckCircle size={20} color="#10B981" />
            <Text style={styles.paidButtonText}>Payé</Text>
          </View>
        )}
      </View>

      {/* Deuxième tranche */}
      <View style={styles.paymentCard}>
        <View style={styles.paymentHeader}>
          <View>
            <Text style={styles.trancheTitle}>Deuxième Tranche</Text>
            <Text style={styles.amount}>25,000 FCFA</Text>
          </View>
          {paymentStatus?.tranche2 ? (
            <CheckCircle size={32} color="#10B981" />
          ) : (
            <Clock size={32} color="#F59E0B" />
          )}
        </View>
        
        <Text style={styles.description}>
          Paiement des frais universitaires - Deuxième partie
        </Text>
        
        {!paymentStatus?.tranche2 ? (
          <TouchableOpacity 
            style={styles.payButton}
            onPress={() => handlePayment(2)}
            disabled={processing}
          >
            <CreditCard size={20} color="white" />
            <Text style={styles.payButtonText}>
              {processing ? 'Traitement...' : 'Payer Maintenant'}
            </Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.paidButton}>
            <CheckCircle size={20} color="#10B981" />
            <Text style={styles.paidButtonText}>Payé</Text>
          </View>
        )}
      </View>

      {/* Résumé total */}
      <View style={styles.summaryCard}>
        <Text style={styles.cardTitle}>Résumé des Paiements</Text>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Total à payer:</Text>
          <Text style={styles.summaryValue}>50,000 FCFA</Text>
        </View>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Montant payé:</Text>
          <Text style={[styles.summaryValue, styles.paidAmount]}>
            {((paymentStatus?.tranche1 ? 25000 : 0) + 
              (paymentStatus?.tranche2 ? 25000 : 0)).toLocaleString()} FCFA
          </Text>
        </View>
        
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Solde restant:</Text>
          <Text style={[styles.summaryValue, styles.remainingAmount]}>
            {(50000 - 
              (paymentStatus?.tranche1 ? 25000 : 0) - 
              (paymentStatus?.tranche2 ? 25000 : 0)).toLocaleString()} FCFA
          </Text>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
  },
  loadingText: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  infoCard: {
    backgroundColor: 'white',
    margin: 16,
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  cardTitle: {
    fontSize: 18,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
    marginBottom: 16,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 6,
  },
  label: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  value: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  paymentCard: {
    backgroundColor: 'white',
    margin: 16,
    marginTop: 0,
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  paymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  trancheTitle: {
    fontSize: 18,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  amount: {
    fontSize: 24,
    fontFamily: 'Inter-Bold',
    color: '#3B82F6',
  },
  description: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginBottom: 16,
  },
  payButton: {
    backgroundColor: '#3B82F6',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 14,
    borderRadius: 12,
    gap: 8,
  },
  payButtonText: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
  },
  paidButton: {
    backgroundColor: '#F0FDF4',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 14,
    borderRadius: 12,
    gap: 8,
    borderWidth: 1,
    borderColor: '#10B981',
  },
  paidButtonText: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#10B981',
  },
  summaryCard: {
    backgroundColor: 'white',
    margin: 16,
    marginTop: 0,
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  summaryLabel: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#475569',
  },
  summaryValue: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  paidAmount: {
    color: '#10B981',
  },
  remainingAmount: {
    color: '#EF4444',
  },
});