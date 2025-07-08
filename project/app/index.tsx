import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { GraduationCap, Shield } from 'lucide-react-native';

/**
 * Page d'accueil - Permet de choisir entre connexion étudiant ou administrateur
 * Cette page sert de point d'entrée principal de l'application
 */
export default function Home() {
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <GraduationCap size={64} color="#3B82F6" />
        <Text style={styles.title}>Système de Paiement</Text>
        <Text style={styles.subtitle}>Université - Gestion des Frais Étudiants</Text>
      </View>

      <View style={styles.buttonContainer}>
        {/* Bouton pour la connexion étudiant */}
        <TouchableOpacity 
          style={[styles.button, styles.studentButton]}
          onPress={() => router.push('/(auth)/student-login')}
        >
          <GraduationCap size={24} color="white" />
          <Text style={styles.buttonText}>Connexion Étudiant</Text>
        </TouchableOpacity>

        {/* Bouton pour la connexion administrateur */}
        <TouchableOpacity 
          style={[styles.button, styles.adminButton]}
          onPress={() => router.push('/(auth)/admin-login')}
        >
          <Shield size={24} color="white" />
          <Text style={styles.buttonText}>Connexion Administrateur</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  header: {
    alignItems: 'center',
    marginBottom: 48,
  },
  title: {
    fontSize: 28,
    fontFamily: 'Inter-Bold',
    color: '#1E293B',
    marginTop: 16,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginTop: 8,
    textAlign: 'center',
  },
  buttonContainer: {
    width: '100%',
    gap: 16,
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 16,
    borderRadius: 12,
    gap: 12,
  },
  studentButton: {
    backgroundColor: '#3B82F6',
  },
  adminButton: {
    backgroundColor: '#10B981',
  },
  buttonText: {
    fontSize: 18,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
  },
});