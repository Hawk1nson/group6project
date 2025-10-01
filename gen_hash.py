# gen_hash.py

import bcrypt
pwd = "Passw0rd!" # CHANGE THIS after initial testing
print(bcrypt.hashpw(pwd.encode(), bcrypt.gensalt()).decode())