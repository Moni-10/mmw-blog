import React, { useEffect, useState } from "react";
import { NavLink, useLocation, useNavigate } from "react-router-dom";
import api from "../api";

const P = {
  overview: <><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></>,
  globe: <><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></>,
  seo: <><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M8 13l2-3 2 2 2-3"/></>,
  create: <><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></>,
  library: <><path d="M4 4h12a3 3 0 0 1 3 3v13H7a3 3 0 0 1-3-3Z"/><path d="M8 9h7M8 13h7"/></>,
  tag: <><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.2"/></>,
  plus: <><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M12 8v8M8 12h8"/></>,
  box: <><path d="M21 8 12 3 3 8v8l9 5 9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/></>,
  folder: <><path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></>,
  user: <><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></>,
};
const NavIcon = ({ name }) => <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{P[name]}</svg>;

const links = [
  ["/", "Overview", "overview"], ["/websites", "Websites", "globe"], ["/seo", "SEO", "seo"],
  ["/add-blog", "Create blog", "create"], ["/edit-blog", "Blog library", "library"], ["/blog-categories", "Blog Categories", "tag"],
  ["/add-product", "Add product", "plus"], ["/products", "Products", "box"], ["/product-categories", "Product Categories", "folder"],
  ["/profile", "Profile", "user"],
];

const Navbar = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [settings, setSettings] = useState({ companyName: "", username: "Administrator", logoUrl: "" });
  const [settingsLoaded, setSettingsLoaded] = useState(false);

  useEffect(() => {
    const loadSettings = () => api.get("/api/settings").then((res) => { setSettings((current) => ({ ...current, ...res.data })); setSettingsLoaded(true); }).catch(() => setSettingsLoaded(true));
    loadSettings();
    window.addEventListener("settings-updated", loadSettings);
    return () => window.removeEventListener("settings-updated", loadSettings);
  }, []);

  if (location.pathname === "/login") return null;
  const logout = () => {
    if (!window.confirm(`Log out from ${settings.companyName} Admin Panel?`)) return;
    localStorage.removeItem("isLoggedIn"); localStorage.removeItem("adminApiKey"); localStorage.removeItem("adminUsername"); navigate("/login");
  };
  const displayName = localStorage.getItem("adminUsername") || settings.username;
  const avatarLetters = (displayName || "AD").slice(0, 2).toUpperCase();

  return <aside className="sidebar">
    <div className="brand mmw-sidebar-brand"><img src="/mmw-logo.png" alt="MMW" /><div><strong>{settings.companyName || "MMW"}</strong><small>Content Manager</small></div></div>
    <nav className="side-nav"><span className="nav-label">Workspace</span>{links.map(([to,label,icon]) => <NavLink key={to} to={to} end={to === "/"} className={({isActive})=>`side-link ${isActive?"active":""}`}><span className="nav-icon"><NavIcon name={icon} /></span>{label}</NavLink>)}</nav>
    <div className="sidebar-foot"><div className="admin-avatar">{avatarLetters}</div><div className="admin-meta"><strong>{displayName}</strong><small>SEO workspace</small></div><button onClick={logout} className="logout-btn" aria-label="Log out" title="Log out">↪</button></div>
    <button onClick={logout} className="sidebar-logout"><span>↪</span> Log out</button>
  </aside>;
};
export default Navbar;
