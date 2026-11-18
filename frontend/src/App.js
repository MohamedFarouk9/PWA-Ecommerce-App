import { Fragment } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from 'react-query';

import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import AppRoutes from './routes/AppRoutes';


const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes stale time to avoid redundant re-fetching
      cacheTime: 15 * 60 * 1000, // 15 minutes in memory cache
      refetchOnWindowFocus: false, // Prevent jarring refetches on window focus
      retry: 1,
    },
  },
});


function App() {
   return (
      <Fragment>
         {/* Wrap your app with QueryClientProvider and pass the queryClient */}
         <QueryClientProvider client={queryClient}>
            <Router>
               <AppRoutes />
            </Router>
            <ToastContainer />
         </QueryClientProvider>
      </Fragment>
   );
}

export default App;
