"""Check that every numbered caption in readme.txt == Screenshots == has a matching assets/screenshot-N.png
in the WP.org SVN checkout (default ../wporg-svn/assets) and vice versa. Usage: python3 tests/readme_screenshots_test.py [assets_dir]"""
import os, re, sys
here = os.path.dirname(os.path.abspath(__file__))
assets = sys.argv[1] if len(sys.argv) > 1 else os.path.join(here, "..", "..", "wporg-svn", "assets")
txt = open(os.path.join(here, "..", "readme.txt"), encoding="utf-8").read()
m = re.search(r"== Screenshots ==\n(.*?)\n== ", txt, re.S)
assert m, "no Screenshots section"
caps = [int(n) for n in re.findall(r"^(\d+)\. ", m.group(1), re.M)]
assert caps == list(range(1, len(caps) + 1)), f"captions not 1..n: {caps}"
files = sorted(int(n) for n in (re.match(r"screenshot-(\d+)\.(png|jpg|jpeg|gif)$", f) and re.match(r"screenshot-(\d+)", f).group(1) for f in os.listdir(assets)) if n)
assert files == caps, f"captions {caps} vs files {files}"
print(f"OK {len(caps)} screenshots")
