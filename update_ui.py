import sys
import re

def update_file(file_path, replacements):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    original = content
    for pattern, repl in replacements:
        if callable(repl):
            content = re.sub(pattern, repl, content, flags=re.DOTALL)
        else:
            content = re.sub(pattern, repl, content, flags=re.DOTALL)

    if original != content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {file_path}")
    else:
        print(f"No changes made to {file_path}")

# 1. Login Blade
login_path = '/var/www/html/onlineBooking/resources/views/auth/login.blade.php'
login_replacements = [
    (r'class="text-\[9px\](.*?)text-slate-500 ml-6"', r'class="text-xs\1text-slate-500 ml-6"'),
    (r'h-18 px-10 bg-slate-50(.*?)text-lg', r'h-14 px-6 bg-slate-50\1text-base')
]
update_file(login_path, login_replacements)

# 2. Vendor Register Blade
vendor_reg_path = '/var/www/html/onlineBooking/resources/views/auth/vendor-register.blade.php'
vendor_reg_replacements = [
    (r'class="text-\[9px\](.*?)text-slate-500 ml-6"', r'class="text-xs\1text-slate-500 ml-6"'),
    (r'h-18 px-10 bg-slate-50(.*?)text-lg', r'h-14 px-6 bg-slate-50\1text-base')
]
update_file(vendor_reg_path, vendor_reg_replacements)

# 3. Vendor Details Blade
details_path = '/var/www/html/onlineBooking/resources/views/customer/vendor-details.blade.php'
details_replacements = [
    (r'class="relative bg-slate-900/90 text-white rounded-\[4rem\] p-12 text-center max-w-xl shadow-\[0_100px_200px_-50px_rgba\(0,0,0,0\.8\)\] border border-white/10"',
     r'class="relative bg-slate-900/90 text-white rounded-[2rem] sm:rounded-[4rem] p-6 sm:p-12 text-center w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-[0_100px_200px_-50px_rgba(0,0,0,0.8)] border border-white/10"'),
    
    (r'(<div class="relative group">\s*<span class="absolute left-6[^>]*>.*?</span>\s*<input type="text" x-model="guestName" class="premium-input w-full )h-16 pl-14( bg-white/5 border-white/10 text-white placeholder-white/20" placeholder="Full \{\{ \$theme\[\'customer_label\'\] \}\} Name">\s*</div>)',
     r'<div class="space-y-2">\n                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 shrink-0 text-left block">Guest Name</label>\n                        \1h-14 pl-12 text-base\2\n                    </div>'),
     
    (r'(<div class="relative group">\s*<span class="absolute left-6[^>]*>.*?</span>\s*<input type="tel" x-model="guestPhone" maxlength="10" class="premium-input w-full )h-16 pl-14( bg-white/5 border-white/10 text-white placeholder-white/20" placeholder="10 Digit Primary Number">\s*</div>)',
     r'<div class="space-y-2">\n                        <label class="text-xs font-black uppercase tracking-[0.2em] text-white/70 ml-2 shrink-0 text-left block">Phone Number</label>\n                        \1h-14 pl-12 text-base\2\n                    </div>')
]
update_file(details_path, details_replacements)
