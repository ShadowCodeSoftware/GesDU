import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { Users, CreditCard, CheckCircle, Clock, DollarSign, TrendingUp } from 'lucide-react-native';
import { adminService } from '@/services/adminService';

/**
 * Dashboard administrateur
 * Vue d'ensemble des statistiques et données importantes
 */
export default function AdminDashboard() {
  const [stats, setStats] = useState({
    totalStudents: 0,
    totalPayments: 0,
    pendingPayments: 0,
    totalAmount: 0,
    paidAmount: 0,
  });
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadStats = async () => {
    try {
      // Récupération des statistiques depuis l'API
      const statistics = await adminService.getDashboardStats();
      setStats(statistics);
    } catch (error) {
      console.error('Erreur lors du chargement des statistiques:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadStats();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadStats();
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Chargement du dashboard...</Text>
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
      {/* En-tête de bienvenue */}
      <View style={styles.welcomeCard}>
        <Text style={styles.welcomeTitle}>Dashboard Administrateur</Text>
        <Text style={styles.welcomeSubtitle}>
          Vue d'ensemble du système de paiement
        </Text>
      </View>

      {/* Cartes de statistiques */}
      <View style={styles.statsGrid}>
        {/* Nombre total d'étudiants */}
        <View style={styles.statCard}>
          <View style={styles.statHeader}>
            <Users size={24} color="#3B82F6" />
            <Text style={styles.statValue}>{stats.totalStudents}</Text>
          </View>
          <Text style={styles.statLabel}>Étudiants Inscrits</Text>
        </View>

        {/* Paiements validés */}
        <View style={styles.statCard}>
          <View style={styles.statHeader}>
            <CheckCircle size={24} color="#10B981" />
            <Text style={styles.statValue}>{stats.totalPayments}</Text>
          </View>
          <Text style={styles.statLabel}>Paiements Validés</Text>
        </View>

        {/* Paiements en attente */}
        <View style={styles.statCard}>
          <View style={styles.statHeader}>
            <Clock size={24} color="#F59E0B" />
            <Text style={styles.statValue}>{stats.pendingPayments}</Text>
          </View>
          <Text style={styles.statLabel}>En Attente</Text>
        </View>

        {/* Montant total collecté */}
        <View style={styles.statCard}>
          <View style={styles.statHeader}>
            <DollarSign size={24} color="#10B981" />
            <Text style={styles.statValue}>
              {stats.paidAmount.toLocaleString()}
            </Text>
          </View>
          <Text style={styles.statLabel}>FCFA Collectés</Text>
        </View>
      </View>

      {/* Résumé financier */}
      <View style={styles.financialCard}>
        <Text style={styles.cardTitle}>Résumé Financier</Text>
        
        <View style={styles.financialRow}>
          <Text style={styles.financialLabel}>Montant total attendu:</Text>
          <Text style={styles.financialValue}>
            {stats.totalAmount.toLocaleString()} FCFA
          </Text>
        </View>
        
        <View style={styles.financialRow}>
          <Text style={styles.financialLabel}>Montant collecté:</Text>
          <Text style={[styles.financialValue, styles.paidAmount]}>
            {stats.paidAmount.toLocaleString()} FCFA
          </Text>
        </View>
        
        <View style={styles.financialRow}>
          <Text style={styles.financialLabel}>Montant restant:</Text>
          <Text style={[styles.financialValue, styles.remainingAmount]}>
            {(stats.totalAmount - stats.paidAmount).toLocaleString()} FCFA
          </Text>
        </View>

        <View style={styles.progressContainer}>
          <Text style={styles.progressLabel}>Taux de collection</Text>
          <View style={styles.progressBar}>
            <View 
              style={[
                styles.progressFill, 
                { width: `${(stats.paidAmount / stats.totalAmount * 100)}%` }
              ]} 
            />
          </View>
          <Text style={styles.progressText}>
            {Math.round(stats.paidAmount / stats.totalAmount * 100)}%
          </Text>
        </View>
      </View>

      {/* Actions rapides */}
      <View style={styles.actionsCard}>
        <Text style={styles.cardTitle}>Actions Rapides</Text>
        <Text style={styles.actionDescription}>
          • Consultez l'onglet "Étudiants" pour gérer les inscriptions{'\n'}
          • Utilisez l'onglet "Paiements" pour valider les transactions{'\n'}
          • Les statistiques se mettent à jour automatiquement
        </Text>
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
    backgroundColor: '#10B981',
    margin: 16,
    padding: 24,
    borderRadius: 16,
  },
  welcomeTitle: {
    fontSize: 22,
    fontFamily: 'Inter-Bold',
    color: 'white',
    marginBottom: 4,
  },
  welcomeSubtitle: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#A7F3D0',
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: 16,
    gap: 12,
  },
  statCard: {
    backgroundColor: 'white',
    flex: 1,
    minWidth: '45%',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  statHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  statValue: {
    fontSize: 24,
    fontFamily: 'Inter-Bold',
    color: '#1E293B',
  },
  statLabel: {
    fontSize: 12,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  financialCard: {
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
  financialRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  financialLabel: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  financialValue: {
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
  progressContainer: {
    marginTop: 16,
  },
  progressLabel: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginBottom: 8,
  },
  progressBar: {
    height: 8,
    backgroundColor: '#F1F5F9',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#10B981',
  },
  progressText: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#10B981',
    textAlign: 'center',
    marginTop: 8,
  },
  actionsCard: {
    backgroundColor: 'white',
    margin: 16,
    marginTop: 0,
    padding: 20,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  actionDescription: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    lineHeight: 20,
  },
});