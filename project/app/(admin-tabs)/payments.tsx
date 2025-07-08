import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Alert, RefreshControl } from 'react-native';
import { CheckCircle, XCircle, Clock, DollarSign, User, Calendar } from 'lucide-react-native';
import { adminService } from '@/services/adminService';

/**
 * Page de validation des paiements
 * Permet aux administrateurs de valider ou rejeter les paiements
 */
export default function PaymentsValidation() {
  const [pendingPayments, setPendingPayments] = useState([]);
  const [validatedPayments, setValidatedPayments] = useState([]);
  const [activeTab, setActiveTab] = useState('pending'); // 'pending' ou 'validated'
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [processing, setProcessing] = useState(false);

  const loadPayments = async () => {
    try {
      // Récupération des paiements en attente et validés
      const [pending, validated] = await Promise.all([
        adminService.getPendingPayments(),
        adminService.getValidatedPayments()
      ]);
      setPendingPayments(pending);
      setValidatedPayments(validated);
    } catch (error) {
      console.error('Erreur lors du chargement des paiements:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadPayments();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadPayments();
  };

  const handleValidatePayment = (paymentId, action) => {
    const actionText = action === 'approve' ? 'valider' : 'rejeter';
    Alert.alert(
      'Confirmer l\'action',
      `Voulez-vous ${actionText} ce paiement ?`,
      [
        { text: 'Annuler', style: 'cancel' },
        { 
          text: 'Confirmer', 
          onPress: () => processPaymentAction(paymentId, action),
          style: action === 'approve' ? 'default' : 'destructive'
        }
      ]
    );
  };

  const processPaymentAction = async (paymentId, action) => {
    setProcessing(true);
    try {
      const result = await adminService.validatePayment(paymentId, action);
      
      if (result.success) {
        Alert.alert(
          'Succès',
          `Paiement ${action === 'approve' ? 'validé' : 'rejeté'} avec succès`,
          [{ text: 'OK', onPress: loadPayments }]
        );
      } else {
        Alert.alert('Erreur', result.message);
      }
    } catch (error) {
      Alert.alert('Erreur', 'Erreur lors de la validation. Veuillez réessayer.');
    } finally {
      setProcessing(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Chargement des paiements...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Onglets */}
      <View style={styles.tabContainer}>
        <TouchableOpacity 
          style={[styles.tab, activeTab === 'pending' && styles.activeTab]}
          onPress={() => setActiveTab('pending')}
        >
          <Text style={[styles.tabText, activeTab === 'pending' && styles.activeTabText]}>
            En Attente ({pendingPayments.length})
          </Text>
        </TouchableOpacity>
        
        <TouchableOpacity 
          style={[styles.tab, activeTab === 'validated' && styles.activeTab]}
          onPress={() => setActiveTab('validated')}
        >
          <Text style={[styles.tabText, activeTab === 'validated' && styles.activeTabText]}>
            Validés ({validatedPayments.length})
          </Text>
        </TouchableOpacity>
      </View>

      {/* Contenu des onglets */}
      <ScrollView 
        style={styles.scrollContainer}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {activeTab === 'pending' ? (
          // Paiements en attente
          <>
            {pendingPayments.map((payment, index) => (
              <View key={payment.id || index} style={styles.paymentCard}>
                <View style={styles.paymentHeader}>
                  <View style={styles.studentInfo}>
                    <View style={styles.avatarContainer}>
                      <User size={20} color="#F59E0B" />
                    </View>
                    <View style={styles.nameContainer}>
                      <Text style={styles.studentName}>
                        {payment.student_name}
                      </Text>
                      <Text style={styles.matricule}>
                        {payment.matricule}
                      </Text>
                    </View>
                  </View>
                  <View style={styles.statusContainer}>
                    <Clock size={20} color="#F59E0B" />
                  </View>
                </View>

                <View style={styles.paymentDetails}>
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Montant:</Text>
                    <Text style={styles.detailValue}>
                      {payment.montant.toLocaleString()} FCFA
                    </Text>
                  </View>
                  
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Tranche:</Text>
                    <Text style={styles.detailValue}>
                      Tranche {payment.tranche}
                    </Text>
                  </View>
                  
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Filière:</Text>
                    <Text style={styles.detailValue}>{payment.filiere}</Text>
                  </View>
                  
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Date:</Text>
                    <Text style={styles.detailValue}>
                      {new Date(payment.date_paiement).toLocaleDateString('fr-FR')}
                    </Text>
                  </View>
                </View>

                <View style={styles.actionButtons}>
                  <TouchableOpacity 
                    style={styles.rejectButton}
                    onPress={() => handleValidatePayment(payment.id, 'reject')}
                    disabled={processing}
                  >
                    <XCircle size={18} color="white" />
                    <Text style={styles.rejectButtonText}>Rejeter</Text>
                  </TouchableOpacity>
                  
                  <TouchableOpacity 
                    style={styles.approveButton}
                    onPress={() => handleValidatePayment(payment.id, 'approve')}
                    disabled={processing}
                  >
                    <CheckCircle size={18} color="white" />
                    <Text style={styles.approveButtonText}>Valider</Text>
                  </TouchableOpacity>
                </View>
              </View>
            ))}

            {pendingPayments.length === 0 && (
              <View style={styles.emptyContainer}>
                <Clock size={48} color="#CBD5E1" />
                <Text style={styles.emptyText}>Aucun paiement en attente</Text>
                <Text style={styles.emptySubtext}>
                  Les nouveaux paiements apparaîtront ici
                </Text>
              </View>
            )}
          </>
        ) : (
          // Paiements validés
          <>
            {validatedPayments.map((payment, index) => (
              <View key={payment.id || index} style={styles.paymentCard}>
                <View style={styles.paymentHeader}>
                  <View style={styles.studentInfo}>
                    <View style={[styles.avatarContainer, styles.validatedAvatar]}>
                      <User size={20} color="#10B981" />
                    </View>
                    <View style={styles.nameContainer}>
                      <Text style={styles.studentName}>
                        {payment.student_name}
                      </Text>
                      <Text style={styles.matricule}>
                        {payment.matricule}
                      </Text>
                    </View>
                  </View>
                  <View style={styles.statusContainer}>
                    <CheckCircle size={20} color="#10B981" />
                  </View>
                </View>

                <View style={styles.paymentDetails}>
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Montant:</Text>
                    <Text style={styles.detailValue}>
                      {payment.montant.toLocaleString()} FCFA
                    </Text>
                  </View>
                  
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Tranche:</Text>
                    <Text style={styles.detailValue}>
                      Tranche {payment.tranche}
                    </Text>
                  </View>
                  
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Validé le:</Text>
                    <Text style={styles.detailValue}>
                      {new Date(payment.date_validation).toLocaleDateString('fr-FR')}
                    </Text>
                  </View>
                </View>
              </View>
            ))}

            {validatedPayments.length === 0 && (
              <View style={styles.emptyContainer}>
                <CheckCircle size={48} color="#CBD5E1" />
                <Text style={styles.emptyText}>Aucun paiement validé</Text>
                <Text style={styles.emptySubtext}>
                  Les paiements validés apparaîtront ici
                </Text>
              </View>
            )}
          </>
        )}
      </ScrollView>
    </View>
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
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: 'white',
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  tab: {
    flex: 1,
    paddingVertical: 16,
    paddingHorizontal: 20,
    alignItems: 'center',
  },
  activeTab: {
    borderBottomWidth: 2,
    borderBottomColor: '#10B981',
  },
  tabText: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#64748B',
  },
  activeTabText: {
    color: '#10B981',
  },
  scrollContainer: {
    flex: 1,
  },
  paymentCard: {
    backgroundColor: 'white',
    margin: 16,
    marginBottom: 8,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    overflow: 'hidden',
  },
  paymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
  },
  studentInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  avatarContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#FEF3C7',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  validatedAvatar: {
    backgroundColor: '#DCFCE7',
  },
  nameContainer: {
    flex: 1,
  },
  studentName: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  matricule: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  statusContainer: {
    marginLeft: 12,
  },
  paymentDetails: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    gap: 8,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  detailLabel: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  detailValue: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  actionButtons: {
    flexDirection: 'row',
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
    gap: 12,
    padding: 16,
  },
  rejectButton: {
    flex: 1,
    backgroundColor: '#EF4444',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 8,
    gap: 8,
  },
  rejectButtonText: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
  },
  approveButton: {
    flex: 1,
    backgroundColor: '#10B981',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 8,
    gap: 8,
  },
  approveButtonText: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 40,
  },
  emptyText: {
    fontSize: 18,
    fontFamily: 'Inter-SemiBold',
    color: '#64748B',
    marginTop: 16,
  },
  emptySubtext: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#94A3B8',
    marginTop: 8,
    textAlign: 'center',
  },
});