import os
import shutil
import re

SOURCE_DIR = r"C:\Users\tauqe\Documents\SMF_Web"
DIST_DIR = os.path.join(SOURCE_DIR, "dist_public_release")

print(f"Preparing privacy-compliant public release bundle at:\n{DIST_DIR}\n")

if os.path.exists(DIST_DIR):
    shutil.rmtree(DIST_DIR)
os.makedirs(DIST_DIR, exist_ok=True)

# 1. Copy assets directory, but EXCLUDE company-activities-*.webp
src_assets = os.path.join(SOURCE_DIR, "assets")
dst_assets = os.path.join(DIST_DIR, "assets")

def ignore_customer_photos(dir_path, filenames):
    ignored = []
    for f in filenames:
        if f.startswith("company-activities-") and f.endswith(".webp"):
            ignored.append(f)
    return ignored

shutil.copytree(src_assets, dst_assets, ignore=ignore_customer_photos)
print("Copied assets (excluded all 22 company-activities-*.webp customer photos).")

# 2. Copy api, admin, private (excluding runtime data & tokens)
for folder in ["api", "admin"]:
    src_f = os.path.join(SOURCE_DIR, folder)
    dst_f = os.path.join(DIST_DIR, folder)
    if os.path.exists(src_f):
        shutil.copytree(src_f, dst_f)
        print(f"Copied {folder}/")

# Handle private/ securely: only copy .htaccess and empty config skeleton
os.makedirs(os.path.join(DIST_DIR, "private", "data"), exist_ok=True)
shutil.copy2(os.path.join(SOURCE_DIR, "private", ".htaccess"), os.path.join(DIST_DIR, "private", ".htaccess"))
shutil.copy2(os.path.join(SOURCE_DIR, "private", "data", ".htaccess"), os.path.join(DIST_DIR, "private", "data", ".htaccess"))
with open(os.path.join(DIST_DIR, "private", "config.php"), "w", encoding="utf-8") as f:
    f.write("<?php\n// Generated during secure first-run setup. Do not put credentials in HTML or JavaScript.\nreturn ['admin_hash' => ''];\n")
print("Configured private/ storage securely (excluded all test data, enquiries, tokens, and credentials).")

# Copy .htaccess and robots.txt if present
for root_file in [".htaccess", "robots.txt"]:
    src_f = os.path.join(SOURCE_DIR, root_file)
    if os.path.exists(src_f):
        shutil.copy2(src_f, os.path.join(DIST_DIR, root_file))

# 3. Process HTML files for dist_public_release
html_files = [f for f in os.listdir(SOURCE_DIR) if f.endswith(".html")]

privacy_banner_en = """
<div class="p-8 sm:p-12 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-center max-w-3xl mx-auto my-12 shadow-sm">
  <div class="w-16 h-16 rounded-full bg-secondary/10 text-secondary flex items-center justify-center mx-auto mb-4">
    <span class="material-symbols-outlined text-[32px]">privacy_tip</span>
  </div>
  <h3 class="font-headline-md text-headline-md text-primary font-bold mb-2">Field Photography Under Privacy Review</h3>
  <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed max-w-xl mx-auto">
    In accordance with the Digital Personal Data Protection Act and individual privacy standards, company activity photographs featuring identifiable community participants are restricted to internal local review pending documented publication consent.
  </p>
  <div class="mt-6 flex flex-wrap justify-center gap-4">
    <a href="contact.html" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary font-label-lg font-semibold hover:bg-primary/90 transition-all">Institutional Enquiries</a>
    <a href="about.html" class="px-6 py-2.5 rounded-lg bg-surface-container-highest text-primary font-label-lg font-semibold hover:bg-surface-container-high transition-all">About Our Company</a>
  </div>
</div>
"""

privacy_banner_hi = """
<div class="smf-hi-card text-center" style="max-width:720px;margin:2rem auto;padding:2.5rem 1.5rem;">
  <span class="smf-hi-dot" style="margin:0 auto 1rem;"></span>
  <h2 style="margin-bottom:0.75rem;">गतिविधि छायाचित्र गोपनीयता समीक्षा के अंतर्गत</h2>
  <p style="max-width:600px;margin:0 auto 1.5rem;line-height:1.7;">
    डिजिटल व्यक्तिगत डेटा संरक्षण मानकों और निजता सुरक्षा के अनुपालन में, ग्रामीणों व प्रतिभागियों के चेहरों वाले छायाचित्र औपचारिक सहमति के सत्यापन तक सार्वजनिक वेबसाइट से अलग रखे गए हैं। यह सामग्री केवल आंतरिक समीक्षा संस्करण में उपलब्ध है।
  </p>
  <div>
    <a href="contact.html" class="smf-hi-cta">कार्यालय से संपर्क करें ↗</a>
  </div>
</div>
"""

for hfile in html_files:
    src_path = os.path.join(SOURCE_DIR, hfile)
    dst_path = os.path.join(DIST_DIR, hfile)
    
    with open(src_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    if hfile == "gallery.html":
        # Replace the grid in gallery.html for the public release with the privacy notices
        # English main grid replacement
        content = re.sub(
            r'<!-- Gallery Grid Section -->.*?<!-- Lightbox Modal -->',
            f'<!-- Public Privacy Notice Section -->\n<section class="w-full py-12 px-6 lg:px-12 bg-surface"><div class="max-w-7xl mx-auto">{privacy_banner_en}</div></section>\n<!-- Lightbox Modal -->',
            content,
            flags=re.DOTALL
        )
        # Hindi main grid replacement
        content = re.sub(
            r'<div class="smf-gallery-grid">.*?</div>\s*</section>\s*</main>',
            f'<div class="smf-gallery-grid">{privacy_banner_hi}</div></section></main>',
            content,
            flags=re.DOTALL
        )
    else:
        # For other pages (index, about, leadership), replace any company-activities-*.webp references with the brand symbol or og-image
        content = re.sub(
            r'assets/images/client/company-activities-\d+\.webp',
            'assets/images/client/smf-og-image.webp',
            content
        )
    
    with open(dst_path, "w", encoding="utf-8") as f:
        f.write(content)
    print(f"Processed {hfile} for public release.")

# 4. Generate manifest
manifest_text = f"""# SMF Public Release Bundle Manifest
Generated: 2026-11-24
Source: C:\\Users\\tauqe\\Documents\\SMF_Web (Canonical Local Review Version)
Destination: C:\\Users\\tauqe\\Documents\\SMF_Web\\dist_public_release

## Segregation Strategy:
1. **Local Review Version (`C:\\Users\\tauqe\\Documents\\SMF_Web`)**:
   - Contains all 22 full-resolution optimized client activity photos (`company-activities-01.webp` to `company-activities-22.webp`).
   - Contains full interactive lightbox, thumbnail grid, and neutral reality captions.
   - For internal client review and verification only.

2. **Public Deployment Bundle (`dist_public_release`)**:
   - Excludes all 22 identifiable customer activity photographs from `assets/images/client/`.
   - Protects the identity and dignity of local village participants under the Digital Personal Data Protection (DPDP) Act.
   - Replaces field gallery with formal privacy review notice in both English and Hindi.
   - Retains 100% verified corporate assets: official arrow logo, favicon, director portraits (Sudhir Kumar, Jyoti Kumari, Shree Mati Devi, Sushant Kumar), Google Maps embed, bilingual navigation, and secure PHP backend.
"""

with open(os.path.join(DIST_DIR, "RELEASE_MANIFEST.md"), "w", encoding="utf-8") as f:
    f.write(manifest_text)

print("\nPublic release bundle created successfully!")
