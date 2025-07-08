import { Stack } from 'expo-router';

/**
 * Layout pour les pages d'authentification
 * Gère la navigation entre les pages de connexion
 */
export default function AuthLayout() {
  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="student-login" />
      <Stack.Screen name="admin-login" />
    </Stack>
  );
}