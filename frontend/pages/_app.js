import { useRouter } from 'next/router';
import PublicNav from '../components/PublicNav';
import '../styles/globals.css';

export default function App({ Component, pageProps }) {
  const router = useRouter();
  const isAdminRoute = router.pathname.startsWith('/admin');

  return (
    <>
      {!isAdminRoute && <PublicNav />}
      <Component {...pageProps} />
    </>
  );
}
