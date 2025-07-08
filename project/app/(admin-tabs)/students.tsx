import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl, TouchableOpacity } from 'react-native';
import { Search, User, BookOpen, MapPin, Calendar } from 'lucide-react-native';
import { adminService } from '@/services/adminService';

/**
 * Page de gestion des étudiants
 * Affiche la liste complète des étudiants avec leurs informations
 */
export default function StudentsManagement() {
  const [students, setStudents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadStudents = async () => {
    try {
      // Récupération de la liste complète des étudiants
      const studentsList = await adminService.getAllStudents();
      setStudents(studentsList);
    } catch (error) {
      console.error('Erreur lors du chargement des étudiants:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadStudents();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadStudents();
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Chargement des étudiants...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* En-tête avec statistiques */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Étudiants Inscrits</Text>
        <Text style={styles.headerSubtitle}>
          {students.length} étudiant{students.length > 1 ? 's' : ''} au total
        </Text>
      </View>

      {/* Liste des étudiants */}
      <ScrollView 
        style={styles.scrollContainer}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {students.map((student, index) => (
          <View key={student.id || index} style={styles.studentCard}>
            {/* En-tête de la carte étudiant */}
            <View style={styles.studentHeader}>
              <View style={styles.avatarContainer}>
                <User size={24} color="#3B82F6" />
              </View>
              <View style={styles.studentInfo}>
                <Text style={styles.studentName}>
                  {student.prenom} {student.nom}
                </Text>
                <Text style={styles.studentMatricule}>
                  Matricule: {student.matricule}
                </Text>
              </View>
              <View style={styles.statusContainer}>
                <View style={[
                  styles.statusBadge, 
                  student.sexe === 'M' ? styles.maleStatus : styles.femaleStatus
                ]}>
                  <Text style={styles.statusText}>{student.sexe}</Text>
                </View>
              </View>
            </View>

            {/* Informations académiques */}
            <View style={styles.academicInfo}>
              <View style={styles.infoRow}>
                <BookOpen size={16} color="#64748B" />
                <Text style={styles.infoText}>
                  {student.filiere} - {student.departement}
                </Text>
              </View>
              
              <View style={styles.infoRow}>
                <Calendar size={16} color="#64748B" />
                <Text style={styles.infoText}>
                  {student.niveau} - {student.annee_academique}
                </Text>
              </View>
              
              <View style={styles.infoRow}>
                <MapPin size={16} color="#64748B" />
                <Text style={styles.infoText}>
                  {student.lieu} ({student.annee_naissance})
                </Text>
              </View>
            </View>

            {/* Statut de paiement */}
            <View style={styles.paymentStatus}>
              <Text style={styles.paymentTitle}>Statut de Paiement</Text>
              <View style={styles.paymentGrid}>
                <View style={styles.paymentItem}>
                  <Text style={styles.paymentLabel}>Tranche 1</Text>
                  <View style={[
                    styles.paymentBadge,
                    student.tranche1_payee ? styles.paidBadge : styles.unpaidBadge
                  ]}>
                    <Text style={[
                      styles.paymentBadgeText,
                      student.tranche1_payee ? styles.paidText : styles.unpaidText
                    ]}>
                      {student.tranche1_payee ? 'Payé' : 'Non payé'}
                    </Text>
                  </View>
                </View>
                
                <View style={styles.paymentItem}>
                  <Text style={styles.paymentLabel}>Tranche 2</Text>
                  <View style={[
                    styles.paymentBadge,
                    student.tranche2_payee ? styles.paidBadge : styles.unpaidBadge
                  ]}>
                    <Text style={[
                      styles.paymentBadgeText,
                      student.tranche2_payee ? styles.paidText : styles.unpaidText
                    ]}>
                      {student.tranche2_payee ? 'Payé' : 'Non payé'}
                    </Text>
                  </View>
                </View>
              </View>
            </View>
          </View>
        ))}

        {students.length === 0 && (
          <View style={styles.emptyContainer}>
            <User size={48} color="#CBD5E1" />
            <Text style={styles.emptyText}>Aucun étudiant inscrit</Text>
            <Text style={styles.emptySubtext}>
              Les nouveaux étudiants apparaîtront ici
            </Text>
          </View>
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
  header: {
    backgroundColor: 'white',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  headerTitle: {
    fontSize: 20,
    fontFamily: 'Inter-Bold',
    color: '#1E293B',
  },
  headerSubtitle: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginTop: 4,
  },
  scrollContainer: {
    flex: 1,
  },
  studentCard: {
    backgroundColor: 'white',
    margin: 16,
    marginBottom: 8,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    overflow: 'hidden',
  },
  studentHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
  },
  avatarContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#EBF8FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  studentInfo: {
    flex: 1,
  },
  studentName: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  studentMatricule: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginTop: 2,
  },
  statusContainer: {
    alignItems: 'center',
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  maleStatus: {
    backgroundColor: '#DBEAFE',
  },
  femaleStatus: {
    backgroundColor: '#FCE7F3',
  },
  statusText: {
    fontSize: 12,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
  academicInfo: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    gap: 8,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  infoText: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#475569',
  },
  paymentStatus: {
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
    padding: 16,
  },
  paymentTitle: {
    fontSize: 14,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
    marginBottom: 12,
  },
  paymentGrid: {
    flexDirection: 'row',
    gap: 12,
  },
  paymentItem: {
    flex: 1,
    alignItems: 'center',
  },
  paymentLabel: {
    fontSize: 12,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginBottom: 4,
  },
  paymentBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    minWidth: 70,
    alignItems: 'center',
  },
  paidBadge: {
    backgroundColor: '#DCFCE7',
  },
  unpaidBadge: {
    backgroundColor: '#FEE2E2',
  },
  paymentBadgeText: {
    fontSize: 12,
    fontFamily: 'Inter-SemiBold',
  },
  paidText: {
    color: '#166534',
  },
  unpaidText: {
    color: '#DC2626',
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