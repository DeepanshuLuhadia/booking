#!/bin/bash
find resources/views/admin resources/views/vendor resources/views/components/admin-layout.blade.php resources/views/components/vendor-layout.blade.php resources/views/components/app-layout.blade.php -type f -name "*.blade.php" -exec sed -i \
-e 's/text-slate-900/text-white/g' \
-e 's/text-slate-800/text-slate-100/g' \
-e 's/text-slate-700/text-slate-200/g' \
-e 's/text-slate-600/text-slate-300/g' \
-e 's/text-slate-500/text-slate-400/g' \
-e 's/bg-white/bg-white\/5/g' \
-e 's/bg-slate-50/bg-white\/5/g' \
-e 's/bg-slate-100/bg-white\/10/g' \
-e 's/border-slate-100/border-white\/10/g' \
-e 's/border-slate-200/border-white\/10/g' \
-e 's/bg-slate-200/bg-white\/10/g' \
-e 's/bg-slate-950/bg-[#0a0f2c]/g' \
{} +
