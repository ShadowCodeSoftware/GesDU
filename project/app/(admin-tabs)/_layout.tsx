import { Tabs } from 'expo-router';
import { Shield, Users, CreditCard, Settings, LogOut } from 'lucide-react-native';
import { TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { authService } from '@/services/authService';

/**
 * Layout pour les onglets administrateur
 * Navigation principale pour les fonctionnalités d'administration
 */
export default function AdminTabsLayout() {
  const handleLogout = async () => {
    await authService.logout();
    router.replace('/');
  };

  return (
    <Tabs
      screenOptions={{
        headerShown: true,
        headerStyle: {
          backgroundColor: '#10B981',
        },
        headerTintColor: 'white',
        headerTitleStyle: {
          fontFamily: 'Inter-SemiBold',
        },
        tabBarActiveTintColor: '#10B981',
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
          title: 'Dashboard Admin',
          tabBarLabel: 'Dashboard',
          tabBarIcon: ({ size, color }) => <Shield size={size} color={color} />,
        }}
      />
      <Tabs.Screen
        name="students"
        options={{
          title: 'Gestion Étudiants',
          tabBarLabel: 'Étudiants',
          tabBarIcon: ({ size, color }) => <Users size={size} color={color} />,
        }}
      />
      <Tabs.Screen
        name="payments"
        options={{
          title: 'Validation Paiements',
          tabBarLabel: 'Paiements',
          tabBarIcon: ({ size, color }) => <CreditCard size={size} color={color} />,
        }}
      />
    </Tabs>
  );
}