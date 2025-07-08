import { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { User, Calendar, MapPin, BookOpen, GraduationCap } from 'lucide-react-native';
import { studentService } from '@/services/studentService';

/**
 * Page de profil étudiant
 * Affiche toutes les informations personnelles et académiques
 */
export default function StudentProfile() {
  const [studentData, setStudentData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const student = await studentService.getCurrentStudent();
      setStudentData(student);
    } catch (error) {
      console.error('Erreur lors du chargement du profil:', error);
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
        <Text style={styles.loadingText}>Chargement du profil...</Text>
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
      {/* En-tête du profil */}
      <View style={styles.profileHeader}>
        <View style={styles.avatarContainer}>
          <User size={48} color="#3B82F6" />
        </View>
        <Text style={styles.fullName}>
          {studentData?.prenom} {studentData?.nom}
        </Text>
        <Text style={styles.matricule}>
          Matricule: {studentData?.matricule}
        </Text>
      </View>

      {/* Informations personnelles */}
      <View style={styles.infoSection}>
        <Text style={styles.sectionTitle}>Informations Personnelles</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <User size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Nom complet</Text>
              <Text style={styles.infoValue}>
                {studentData?.prenom} {studentData?.nom}
              </Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <User size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Sexe</Text>
              <Text style={styles.infoValue}>{studentData?.sexe}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <Calendar size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Année de naissance</Text>
              <Text style={styles.infoValue}>{studentData?.annee_naissance}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <MapPin size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Lieu de naissance</Text>
              <Text style={styles.infoValue}>{studentData?.lieu}</Text>
            </View>
          </View>
        </View>
      </View>

      {/* Informations académiques */}
      <View style={styles.infoSection}>
        <Text style={styles.sectionTitle}>Informations Académiques</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <BookOpen size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Filière</Text>
              <Text style={styles.infoValue}>{studentData?.filiere}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <GraduationCap size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Département</Text>
              <Text style={styles.infoValue}>{studentData?.departement}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <Calendar size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Niveau</Text>
              <Text style={styles.infoValue}>{studentData?.niveau}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <Calendar size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Année académique</Text>
              <Text style={styles.infoValue}>{studentData?.annee_academique}</Text>
            </View>
          </View>
        </View>
      </View>

      {/* Informations système */}
      <View style={styles.infoSection}>
        <Text style={styles.sectionTitle}>Informations Système</Text>
        
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <User size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Matricule</Text>
              <Text style={styles.infoValue}>{studentData?.matricule}</Text>
            </View>
          </View>
          
          <View style={styles.infoRow}>
            <Calendar size={20} color="#64748B" />
            <View style={styles.infoContent}>
              <Text style={styles.infoLabel}>Date d'inscription</Text>
              <Text style={styles.infoValue}>
                {studentData?.date_inscription ? 
                  new Date(studentData.date_inscription).toLocaleDateString('fr-FR') : 
                  'Non disponible'
                }
              </Text>
            </View>
          </View>
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
  profileHeader: {
    backgroundColor: 'white',
    alignItems: 'center',
    padding: 32,
    marginBottom: 16,
  },
  avatarContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#EBF8FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  fullName: {
    fontSize: 24,
    fontFamily: 'Inter-Bold',
    color: '#1E293B',
    marginBottom: 4,
  },
  matricule: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
  },
  infoSection: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
    marginBottom: 12,
    marginHorizontal: 16,
  },
  infoCard: {
    backgroundColor: 'white',
    marginHorizontal: 16,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    padding: 16,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  infoContent: {
    flex: 1,
    marginLeft: 12,
  },
  infoLabel: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: '#1E293B',
  },
});