import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { CreditCard, CheckCircle, AlertCircle, Calendar, MapPin } from 'lucide-react-native';
import { studentService } from '@/services/studentService';

/**
 * Dashboard étudiant - Page d'accueil pour les étudiants
 * Affiche les informations personnelles et le statut des paiements
 */
export default function StudentDashboard() {
  const [studentData, setStudentData] = useState(null);
  const [paymentStatus, setPaymentStatus] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      // Récupération des données étudiant et statut de paiement
      const [student, payments] = await Promise.all([
        studentService.getCurrentStudent(),
        studentService.getPaymentStatus()
      ]);
      setStudentData(student);
      setPaymentStatus(payments);
    } catch (error) {
      console.error('Erreur lors du chargement des données:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Chargement...</Text>
      </View>
    );
  }

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
      }
    >
      {/* Carte de bienvenue */}
      <View style={styles.welcomeCard}>
        <Text style={styles.welcomeText}>
          Bonjour, {studentData?.prenom} {studentData?.nom}
        </Text>
        <Text style={styles.matriculeText}>
          Matricule: {studentData?.matricule}
        </Text>
      </View>

      {/* Statut des paiements */}
      <View style={styles.paymentStatusCard}>
        <Text style={styles.cardTitle}>Statut des Paiements</Text>
        
        <View style={styles.paymentRow}>
          <View style={styles.paymentInfo}>
            <Text style={styles.paymentLabel}>Première Tranche</Text>
            <Text style={styles.paymentAmount}>25,000 FCFA</Text>
          </View>
          {paymentStatus?.tranche1 ? (
            <CheckCircle size={24} color="#10B981" />
          ) : (
            <AlertCircle size={24} color="#EF4444" />
          )}
        </View>

        <View style={styles.paymentRow}>
          <View style={styles.paymentInfo}>
            <Text style={styles.paymentLabel}>Deuxième Tranche</Text>
            <Text style={styles.paymentAmount}>25,000 FCFA</Text>
          </View>
          {paymentStatus?.tranche2 ? (
            <CheckCircle size={24} color="#10B981" />
          ) : (
            <AlertCircle size={24} color="#EF4444" />
          )}
        </View>

        <View style={styles.totalRow}>
          <Text style={styles.totalLabel}>Total Payé:</Text>
          <Text style={styles.totalAmount}>
            {((paymentStatus?.tranche1 ? 25000 : 0) + 
              (paymentStatus?.tranche2 ? 25000 : 0)).toLocaleString()} FCFA
          </Text>
        </View>
      </View>

      {/* Informations académiques */}
      <View style={styles.infoCard}>
        <Text style={styles.cardTitle}>Informations Académiques</Text>
        
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Filière:</Text>
          <Text style={styles.infoValue}>{studentData?.filiere}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Département:</Text>
          <Text style={styles.infoValue}>{studentData?.departement}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Niveau:</Text>
          <Text style={styles.infoValue}>{studentData?.niveau}</Text>
        </View>
        
        <View style={styles.infoRow}>
          <Text style={styles.infoLabel}>Année Académique:</Text>
          <Text style={styles.infoValue}>{studentData?.annee_academique}</Text>
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
  welcomeCard: {
    backgroundColor: '#3B82F6',
    margin: 16,
    padding: 20,
    borderRadius: 16,
  },
  welcomeText: {
    fontSize: 20,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
    marginBottom: 4,
  },
  matriculeText: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#BFDBFE',
  },
  paymentStatusCard: {
    backgroundColor: 'white',
    margin: 16,
    marginTop: 0,
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
  paymentRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  paymentInfo: {
    flex: 1,
  },
  paymentLabel: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#475569',
  },
  paymentAmount: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 16,
  },
  totalLabel: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  totalAmount: {
    fontSize: 18,
    fontFamily: 'Inter-Bold',
    color: '#10B981',
  },
  infoCard: {
    backgroundColor: 'white',
    margin: 16,
    marginTop: 0,
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  infoLabel: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  infoValue: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
});