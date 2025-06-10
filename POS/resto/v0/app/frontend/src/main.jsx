import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import "@fontsource/inter";

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter basename="/POS/resto/v1/">
    <App />
  </BrowserRouter>
</StrictMode>,
)
