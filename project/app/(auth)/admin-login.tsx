import { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { router } from 'expo-router';
import { ArrowLeft, Shield, Lock } from 'lucide-react-native';
import { authService } from '@/services/authService';

/**
 * Page de connexion pour les administrateurs
 * Identifiant par défaut: admin
 * Mot de passe par défaut: admin
 */
export default function AdminLogin() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!username || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    setLoading(true);
    try {
      // Appel au service d'authentification admin
      const result = await authService.loginAdmin(username, password);
      if (result.success) {
        // Redirection vers le dashboard administrateur
        router.replace('/(admin-tabs)');
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
        <Text style={styles.title}>Administration</Text>
        <Text style={styles.subtitle}>Accès réservé aux administrateurs</Text>

        <View style={styles.form}>
          {/* Champ identifiant */}
          <View style={styles.inputContainer}>
            <Shield size={20} color="#64748B" />
            <TextInput
              style={styles.input}
              placeholder="Identifiant (admin)"
              value={username}
              onChangeText={setUsername}
              autoCapitalize="none"
            />
          </View>

          {/* Champ mot de passe */}
          <View style={styles.inputContainer}>
            <Lock size={20} color="#64748B" />
            <TextInput
              style={styles.input}
              placeholder="Mot de passe (admin)"
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

        {/* Information sur les identifiants par défaut */}
        <View style={styles.infoBox}>
          <Text style={styles.infoText}>
            Identifiants par défaut:{'\n'}
            Nom d'utilisateur: admin{'\n'}
            Mot de passe: admin
          </Text>
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
    marginBottom: 32,
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
    backgroundColor: '#10B981',
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
  infoBox: {
    backgroundColor: '#FEF3C7',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#F59E0B',
  },
  infoText: {
    fontSize: 14,
    fontFamily: 'Inter-Regular',
    color: '#92400E',
    textAlign: 'center',
  },
});