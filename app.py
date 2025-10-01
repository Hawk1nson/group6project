#app.py

import streamlit as st
import pandas as pd
import datetime as dt
from db_config import get_conn
from auth import fetch_user_by_email, verify_password   

def fetch_df(sql, params=None):
    conn = get_conn()
    df = pd.read_sql(sql, conn, params=params or [])
    conn.close()
    return df

st.set_page_config(page_title="CDMS Dashboard", layout="wide")

# -- Login (simple) --
if "user" not in st.session_state:
    st.session_state.user = None

if not st.session_state.user:
    st.title("CDMS Login")
    with st.form("login", clear_on_submit=False):
        email = st.text_input("Email")
        password = st.text_input("Password", type="password")
        submitted = st.form_submit_button("Sign in")
    if submitted:
        user = fetch_user_by_email(email.strip())
        if user and verify_password(password, user["password_hash"]):
            st.session_state.user = {
                "employee_id": user["employee_id"],
                "name": f'{user["first_name"]} {user["last_name"]}',
                "email": user["email"],
                "role": user["role"],
            }
            st.success("Signed in. Loading dashboard…")
            st.rerun()
        else:
            st.error("Invalid email or password.")
    st.stop()  # <-- prevents rest of app from rendering until logged in

# ---- TOP BAR (shows who is logged in) ----
colA, colB = st.columns([3,1], vertical_alignment="center")
with colA:
    st.caption(f"Signed in as {st.session_state.user['name']} • {st.session_state.user['role']}")
with colB:
    if st.button("Log out", use_container_width=True):
        st.session_state.user = None
        st.rerun()

# Sidebar navigation
page = st.sidebar.radio("View", ["Inventory", "Reservations", "Sales"])

if page == "Inventory":
    st.title("Inventory")
    df = fetch_df("SELECT make, model, model_year, price, status FROM vehicles ORDER BY price ASC;")
    st.dataframe(df, use_container_width=True)

elif page == "Reservations":
    st.title("Reservations")
    df = fetch_df("""
        SELECT r.reservation_id, r.status, r.start_datetime, r.end_datetime,
               CONCAT(c.first_name,' ',c.last_name) AS customer,
               v.make, v.model, v.model_year
        FROM reservations r
        JOIN customers c ON c.customer_id = r.customer_id
        JOIN vehicles v ON v.vehicle_id = r.vehicle_id
        ORDER BY r.start_datetime ASC;
    """)
    st.dataframe(df, use_container_width=True)

elif page == "Sales":
    st.title("Sales")
    start = st.date_input("Start date", value=dt.date.today().replace(day=1))
    end = st.date_input("End date", value=dt.date.today())
    df = fetch_df("""
        SELECT s.sale_date, s.sale_price,
               v.make, v.model, v.model_year,
               CONCAT(e.first_name,' ',e.last_name) AS salesperson,
               CONCAT(c.first_name,' ',c.last_name) AS customer
        FROM sales s
        JOIN vehicles v ON v.vehicle_id = s.vehicle_id
        JOIN employees e ON e.employee_id = s.employee_id
        JOIN customers c ON c.customer_id = s.customer_id
        WHERE s.sale_date BETWEEN %s AND %s
        ORDER BY s.sale_date DESC;
    """, [start, end])
    st.dataframe(df, use_container_width=True)
