
export const useAuth = () => {
  const token = useCookie('auth_token');
  return { token };
};
