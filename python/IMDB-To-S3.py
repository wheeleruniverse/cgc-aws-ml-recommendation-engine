#!/usr/bin/env python
# coding: utf-8

# In[1]:


import gzip
import shutil
import urllib.request


# In[2]:


def download_gz(url, filename):
    """
    downloads a GunZip file from the url given and writes it to the local filesystem
    :param url: the url of the GunZipped file to download
    :param filename: the filename to write to the local filesystem
    :return: None
    """
    
    if url is None or not url.endswith(".gz"):
        raise ValueError("'url' is invalid")
    
    print(f"Downloading: {url}")
    
    with urllib.request.urlopen(url) as response, open(filename, 'wb') as output_file:
        with gzip.GzipFile(fileobj=response) as uncompressed:
            shutil.copyfileobj(uncompressed, output_file)
            
            print(f"Downloaded : {filename}")
            print()


# In[3]:


imdb_root = "https://datasets.imdbws.com"
imdb_sets = [
    "name.basics.tsv.gz",
    "title.akas.tsv.gz",
    "title.basics.tsv.gz",
    "title.crew.tsv.gz",
    "title.episode.tsv.gz",
    "title.principals.tsv.gz",
    "title.ratings.tsv.gz"
]


# In[4]:


for name in imdb_sets:
    download_gz(f"{imdb_root}/{name}", name.replace(".gz", ""))


# In[ ]:




