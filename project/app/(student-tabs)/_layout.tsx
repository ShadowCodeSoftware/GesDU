import { Tabs } from 'expo-router';
import { Home, CreditCard, User, LogOut } from 'lucide-react-native';
import { TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { authService } from '@/services/authService';

/**
 * Layout pour les onglets étudiants
 * Navigation principale pour les fonctionnalités étudiants
 */
export default function StudentTabsLayout() {
  const handleLogout = async () => {
    await authService.logout();
    router.replace('/');
  };

  return (
    <Tabs
      screenOptions={{
        headerShown: true,
        headerStyle: {
          backgroundColor: '#3B82F6',
        },
        headerTintColor: 'white',
        headerTitleStyle: {
          fontFamily: 'Inter-SemiBold',
        },
        tabBarActiveTintColor: '#3B82F6',
        tabBarInactiveTintColor: '#64748B',
        tabBarStyle: {
          backgroundColor: 'white',
          borderTopColor: '#E2E8F0',
        },
        headerRight: () => (
          <TouchableOpacity onPress={handleLogout} style={{ marginRight: 16 }}>
            <LogOut size={24} color="white" />
          </TouchableOpacity>
        ),
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Tableau de Bord',
          tabBarLabel: 'Accueil',
          tabBarIcon: ({ size, color }) => <Home size={size} color={color} />,
        }}
      />
      <Tabs.Screen
        name="payment"
        options={{
          title: 'Paiements',
          tabBarLabel: 'Paiements',
          tabBarIcon: ({ size, color }) => <CreditCard size={size} color={color} />,
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profil',
          tabBarLabel: 'Profil',
          tabBarIcon: ({ size, color }) => <User size={size} color={color} />,
        }}
      />
    </Tabs>
  );
}