import { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { router } from 'expo-router';
import { ArrowLeft, User, Lock } from 'lucide-react-native';
import { authService } from '@/services/authService';

/**
 * Page de connexion pour les étudiants
 * Permet aux étudiants de se connecter avec leur matricule et mot de passe
 */
export default function StudentLogin() {
  const [matricule, setMatricule] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!matricule || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    setLoading(true);
    try {
      // Appel au service d'authentification
      const result = await authService.loginStudent(matricule, password);
      if (result.success) {
        // Redirection vers le dashboard étudiant
        router.replace('/(student-tabs)');
      } else {
        Alert.alert('Erreur', result.message);
      }
    } catch (error) {
      Alert.alert('Erreur', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      {/* Bouton de retour */}
      <TouchableOpacity 
        style={styles.backButton}
        onPress={() => router.back()}
      >
        <ArrowLeft size={24} color="#64748B" />
      </TouchableOpacity>

      <View style={styles.content}>
        <Text style={styles.title}>Connexion Étudiant</Text>
        <Text style={styles.subtitle}>Connectez-vous avec votre matricule</Text>

        <View style={styles.form}>
          {/* Champ matricule */}
          <View style={styles.inputContainer}>
            <User size={20} color="#64748B" />
            <TextInput
              style={styles.input}
              placeholder="Matricule"
              value={matricule}
              onChangeText={setMatricule}
              autoCapitalize="none"
            />
          </View>

          {/* Champ mot de passe */}
          <View style={styles.inputContainer}>
            <Lock size={20} color="#64748B" />
            <TextInput
              style={styles.input}
              placeholder="Mot de passe"
              value={password}
              onChangeText={setPassword}
              secureTextEntry
            />
          </View>

          {/* Bouton de connexion */}
          <TouchableOpacity 
            style={[styles.loginButton, loading && styles.loginButtonDisabled]}
            onPress={handleLogin}
            disabled={loading}
          >
            <Text style={styles.loginButtonText}>
              {loading ? 'Connexion...' : 'Se connecter'}
            </Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
    paddingTop: 60,
  },
  backButton: {
    marginLeft: 24,
    marginBottom: 24,
  },
  content: {
    flex: 1,
    paddingHorizontal: 24,
  },
  title: {
    fontSize: 32,
    fontFamily: 'Inter-Bold',
    color: '#1E293B',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#64748B',
    marginBottom: 32,
  },
  form: {
    gap: 16,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'white',
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    gap: 12,
  },
  input: {
    flex: 1,
    fontSize: 16,
    fontFamily: 'Inter-Regular',
    color: '#1E293B',
  },
  loginButton: {
    backgroundColor: '#3B82F6',
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 8,
  },
  loginButtonDisabled: {
    opacity: 0.6,
  },
  loginButtonText: {
    fontSize: 16,
    fontFamily: 'Inter-SemiBold',
    color: 'white',
  },
});