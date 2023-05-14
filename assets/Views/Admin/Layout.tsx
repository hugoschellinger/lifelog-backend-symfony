import React from "react";
import style from "./Layout.module.scss";
import { Outlet } from "react-router";
import Logo from "../../Assets/image/logo.png";
import { NavLink } from "react-router-dom";

const Layout = () => {
  return (
    <div className={style.container}>
      <div className={style.sidebar}>
        <div className={style.headerSidebar}>
          <img src={Logo} className={style.logoImg} />
          <p className={style.title}>Dashboard</p>
        </div>
        <div className={style.navigation}>
          <div className={style.navItem} style={{ cursor: "pointer" }}>
            <NavLink to={"/admin/overview"}>
              <p
                style={{
                  fontSize: "1.5rem",
                  marginLeft: "1rem",
                  fontWeight: 600,
                }}
              >
                Overview
              </p>
            </NavLink>
          </div>
          <div className={style.userGroup}>
            <div className={style.navGroupItem}>
              <p>Users</p>
            </div>
            <div>
              <NavLink to={"/admin/users/overview"} className={style.navItem}>
                <p>Overview</p>
              </NavLink>
            </div>
            <div>
              <NavLink to={"/admin/users/list"} className={style.navItem}>
                <p>List</p>
              </NavLink>
            </div>
            <div>
              <NavLink to={"/admin/users/create"} className={style.navItem}>
                <p>Create</p>
              </NavLink>
            </div>
          </div>
          <div className={style.ErrorGroup}>
            <div className={style.navGroupItem}>
              <p>Error</p>
            </div>
            <div>
              <NavLink to={"/admin/errors/overview"} className={style.navItem}>
                <p>Overview</p>
              </NavLink>
            </div>
          </div>
        </div>
      </div>
      <div style={{ flex: 1 }}>
        <Outlet />
      </div>
    </div>
  );
};

export default Layout;
