import { useState, useContext } from "react";
import { Link, useLocation } from "react-router-dom";
import {
  HomeIcon,
  CreditCardIcon,
  CurrencyDollarIcon,
  CubeIcon,
  UsersIcon,
  Bars3Icon,
  XMarkIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  Cog6ToothIcon,
  QuestionMarkCircleIcon,
  ArrowLeftCircleIcon,
  ChartPieIcon,
  PresentationChartBarIcon,
  ClipboardDocumentListIcon,
  ReceiptPercentIcon,
  ListBulletIcon,
} from "@heroicons/react/24/outline";
import { Dialog } from "@headlessui/react";
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { AuthContext } from '../contexts/AuthContext';
import { useRequireAuth } from '../contexts/AuthContext';// Adjust the import path as necessary

const links = [
  { name: "Dashboard", path: "/Admin_dashboard", icon: PresentationChartBarIcon },
  { name: "Orders", path: "/Admin_orders", icon: ClipboardDocumentListIcon },
  { name: "Menu Items", path: "/Admin_menu", icon: ListBulletIcon },
  { name: "Users", path: "/Admin_users", icon: UsersIcon },
  { name: "Reports", path: "/Admin_reports", icon: ChartPieIcon },

];

function NavBar() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const location = useLocation();
  const user = useRequireAuth();
  const { logout } = useContext(AuthContext);

  const handleSignOut = (e) => {
    e.preventDefault();
    logout();
  };

  const isActive = (path) => location.pathname === path;

  return (
    <>
      {/* Mobile Sidebar */}
      <Dialog
        as="div"
        className="relative z-40 md:hidden"
        open={sidebarOpen}
        onClose={setSidebarOpen}
      >
        <div className="fixed inset-0 bg-black/60" />
        <div className="fixed inset-y-0 w-full max-w-70 p-2 transition duration-300 ease-in-out data-closed:-translate-x-full">
          <div className="flex h-full flex-col rounded-lg bg-white shadow-xs ring-1 ring-zinc-950/5">
            <div className="mb-3 px-4 pt-3">
              <span className="relative">
                <button
                  onClick={() => setSidebarOpen(false)}
                  className="ml-1 items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                >
                  <XMarkIcon className="h-6 w-6 text-red" />
                </button>
              </span>

            </div>
            <div className="pt-5 pb-4">
              <div className="text-l text-center">
                R-Click Solutions
              </div>
              <div className="text-xs italic text-center">
                Resto POS
              </div>
              <div className="text-xs text-center mb-5">
                Inciongs Bistro & Cafe
              </div>
              <nav className="space-y-1 px-4">
                {links.map((link) => (
                  <Link
                    key={link.name}
                    to={link.path}
                    className={`flex items-center px-3 py-2 text-sm font-medium gap-3 text-black${isActive(link.path)
                        ? "border-l-2 border-white font-bold"
                        : "text-gray-400 hover:border-l-2 hover:border-black hover:text-gray-400"
                      }`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <link.icon className="h-5 w-5 text-black" />
                    {link.name}
                  </Link>
                ))}
              </nav>
            </div>
          </div>
          <div className="flex-shrink-0 w-14" aria-hidden="true" />
        </div>
      </Dialog>

      {/* Desktop Sidebar */}
      <div className="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-[#2B4F4B]">
        <div className="flex-1 flex flex-col pt-5">
          <div className="text-l text-center text-white">
            R-Click Solutions
          </div>
          <div className="text-xs text-center text-white italic">
            Resto POS
          </div>
          <div className="text-xs text-center mb-5 text-white">
            Inciongs Bistro & Cafe
          </div>
          <nav className="flex-1 px-2">
            {links.map((link) => (
              <Link
                key={link.name}
                to={link.path}
                className={`flex items-center px-3 py-2 text-sm font-medium gap-3 text-white ${isActive(link.path)
                    ? "border-l-2 border-white font-bold"
                    : "text-gray-400 hover:border-l-2 hover:border-white hover:text-white"

                  }`}
              >
                <link.icon className="h-5 w-5 text-white" />
                {link.name}
              </Link>
            ))}
          </nav>

          <div className="sticky top-0 h-16 justify-center">
            <Menu as="div" className="text-left">
              <MenuButton className='w-full'>
                <span className="flex p-3 items-center gap-2 hover:border-l-2 border-white">
                  <span>
                    <img className="h-10 w-10 rounded-full object-cover "
                      src="assets/RENZIE.png"
                      alt="User Avatar"
                    />
                  </span>
                  <span className="min-w-0 mx-auto text-end">
                    <span className="block truncate text-sm/5 font-medium text-white">
                      {user?.name}
                    </span>
                    <span className="block truncate text-xs/5 font-normal text-white">
                      {user?.role}
                    </span>
                  </span>
                  <ChevronUpIcon className="h-5 w-5 min-w-0 text-white mx-auto" />
                </span>
              </MenuButton>
              <MenuItems
                transition
                className="absolute right-0 z-10 mb-2 w-45 origin-bottom-right rounded-md bg-white shadow-lg ring-1 ring-black/5 transition focus:outline-hidden bottom-full data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in"
              >
                <div className="py-1">
                  <MenuItem>
                    <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                    ><Cog6ToothIcon className="size-5 text-gray-600" />
                      Account settings
                    </a>
                  </MenuItem>
                  <MenuItem>
                    <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                    ><QuestionMarkCircleIcon className="size-5 text-gray-600" />
                      Support
                    </a>
                  </MenuItem>
                  <MenuItem>
                    <button
                      onClick={handleSignOut}
                      className="flex w-full items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                    >
                      <ArrowLeftCircleIcon className="size-5 text-gray-600" />
                      Sign out
                    </button>
                  </MenuItem>
                </div>
              </MenuItems>
            </Menu>
          </div>
        </div>
      </div>

      {/* Mobile Top Bar */}
      <div className="md:hidden sticky top-0 z-10 flex h-16 bg-[#2B4F4B] shadow items-center justify-between px-4">
        <div className="flex items-center gap-2">
          <button
            className="text-white"
            onClick={() => setSidebarOpen(true)}
          >
            <Bars3Icon className="h-6 w-6" />
          </button>
        </div>

        <div className="sticky top-0  flex h-full justify-center">
          <Menu as="div" className="">
            <MenuButton className="h-full">
              <span className="flex min-w-0 items-center gap-1 hover:">
                <span className="min-w-0 text-end">
                  <span className="block truncate text-sm/5 font-medium text-white">
                    {user?.name}
                  </span>
                  <span className="block truncate text-xs/5 font-normal text-white">
                    {user?.role}
                  </span>
                </span>
                <span>
                  <img className="h-10 w-10 rounded-full object-cover"
                    src="src/assets/RENZIE.png"
                    alt="User Avatar"
                  />
                </span>
                <ChevronDownIcon className="h-5 w-5 min-w-0 text-white mx-auto" />
              </span>
            </MenuButton>
            <MenuItems
              transition
              className="absolute right-0 z-10 mt-2 w-45 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 transition focus:outline-hidden data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in"
            >
              <div className="py-1">
                <MenuItem>
                  <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                  ><Cog6ToothIcon className="size-5 text-gray-600" />
                    Account settings
                  </a>
                </MenuItem>
                <MenuItem>
                  <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                  ><QuestionMarkCircleIcon className="size-5 text-gray-600" />
                    Support
                  </a>
                </MenuItem>
                <MenuItem>
                  <button
                    onClick={handleSignOut}
                    className="flex w-full items-center gap-2 px-2 py-2 text-sm text-gray-700 data-focus:border-l-2 border-black data-focus:text-gray-900 data-focus:outline-hidden"
                  >
                    <ArrowLeftCircleIcon className="size-5 text-gray-600" />
                    Sign out
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
      </div>
    </>
  );
}

export default NavBar;
