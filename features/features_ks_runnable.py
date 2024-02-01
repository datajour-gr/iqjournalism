#!/usr/bin/env python
import pandas as pd
import numpy as np
import requests
import os
import pandas as pd
import numpy as np
import nltk
from collections import Counter
import nltk.sentiment
import spacy
# from spacy import displacy 
from spacy.matcher import Matcher
import pandas as pd
import unicodedata
from unidecode import unidecode
from collections import defaultdict
import textdescriptives as td  #TEXTDESCRIPTIVES FR PSACY3
import numpy as np
# import eli5
# import graphviz
import re
import sys

if len(sys.argv) != 2:
    print("Usage: python optimized_final_features_high.py <filename>")
    exit()
else:
    filename = sys.argv[1]
    print(f"Processing file: {filename}")

AIL_df = pd.read_csv("/home/iqjournalism/iq_journalism/LEXIKA/AIL_KS.csv")
nlp = spacy.load("el_core_news_lg")
pd.options.display.max_columns
pd.set_option("display.max_columns",100)

df_news = pd.read_csv(filename)

#df_letter['num_wds'] = df_letter['tokenized'].apply(lambda x: len(x.split()))
df_news['num_wds'] = df_news['article_text'].apply(lambda x: len(x.split()) if pd.notna(x) and type(x) == str else 0)


# In[315]:
df_news['num_wds'].describe()


ax=df_news['num_wds'].plot(kind='hist', bins=5, fontsize=14, figsize=(6,5))
ax.set_title('Text length\n', fontsize=10)
ax.set_ylabel('Frequency', fontsize=10)
ax.set_xlabel('Number of Words', fontsize=10)


#drop rows with texts< 60 words
df_news = df_news[df_news['num_wds'] >= 60]


df_news = df_news.reset_index()


df_news['uniq_wds'] = df_news['article_text'].apply(lambda x: len(set(x.split())) if pd.notna(x) and type(x) == str else 0)
df_news['uniq_wds'].head()

ax=df_news['uniq_wds'].plot(kind='hist', bins=50, fontsize=14, figsize=(12,10))
ax.set_title('Unique Words Per Text\n', fontsize=20)
ax.set_ylabel('Frequency', fontsize=18)
ax.set_xlabel('Number of Unique Words', fontsize=18)


# ## Lexical Richness

# In[322]:


def calculate_mtld(text, chunk_size=100):
    tokens = text.split()  # Tokenize the text (simple splitting by whitespace)

    unique_words = set()  # Track unique words
    mtld_count = 0
    total_tokens = len(tokens)

    for i, token in enumerate(tokens):
        unique_words.add(token)

        if len(unique_words) >= chunk_size:
            mtld_count += 1
            unique_words.clear()

    # Calculate MTLD score
    mtld_score = total_tokens / (mtld_count + 1)

    return mtld_score

df_news['MTLD'] = df_news['article_text'].apply(lambda x: calculate_mtld(x))


# Function to process text and count nouns, verbs, and adjectives
def find_nouns_verbs_adjectives(text):
    doc = nlp(text)
    nouns = sum(1 for token in doc if token.pos_ == "NOUN")
    verbs = sum(1 for token in doc if token.pos_ == "VERB")
    adjectives = sum(1 for token in doc if token.pos_ == "ADJ")
    return nouns, verbs, adjectives


# Apply the function to each row of the DataFrame
df_news[["Nouns", "Verbs", "Adjectives"]] = df_news["article_text"].apply(lambda x: pd.Series(find_nouns_verbs_adjectives(x)))
df_news["Nouns"] = df_news["Nouns"]/ df_news['num_wds'] *100
df_news["Verbs"] = df_news["Verbs"]/ df_news['num_wds'] *100
df_news["Adjectives"] = df_news["Adjectives"]/ df_news['num_wds'] *100

w2affect = []
for w,score,dim in AIL_df.values:
    w2affect.append({dim:score,'w':w})
w2affects = {}
df = pd.DataFrame(w2affect)
df = df.groupby('w').sum().reset_index()
for w,anger,fear,joy,sadness, trust, disgust, anticipation, surprise in df[['w','anger','fear','joy','sadness', 'trust', 'disgust', 'anticipation', 'surprise']].values:
    w2affects[w] = {'anger':anger,'joy':joy,'sadness':sadness,'fear':fear,'trust': trust, 'disgust':disgust, 'anticipation':anticipation,'surprise': surprise }

def get_affect_intensity_score(doc,tokenizer=nltk.word_tokenize,agg='mean'):
    if type(doc)==str:
        doc = tokenizer(doc)
    assert type(doc)==list,"please input either a list or a string"
    matches = []
    for w in doc:
        w = w.lower()
        if w in w2affects:
            matches.append(w2affects[w])
    if len(matches)==0:
        return {'anger':np.nan,'joy':np.nan,'sadness':np.nan,'fear':np.nan,'trust':np.nan,'disgust':np.nan,'anticipation':np.nan,'surprise':np.nan}
    scores = pd.DataFrame(matches)
    if agg=='mean':
        scores = scores.mean()
    elif agg=='max':
        scores =  scores.max()
    else:
        scores = agg(scores)
    return dict(scores)


w2affect = []
for w,score,dim in AIL_df.values:
    w2affect.append({dim:score,'w':w})
w2affects = {}
df = pd.DataFrame(w2affect)
df = df.groupby('w').sum().reset_index()
for w,anger,fear,joy,sadness in df[['w','anger','fear','joy','sadness']].values:
    w2affects[w] = {'anger':anger,'joy':joy,'sadness':sadness,'fear':fear}

def get_anger_score(doc,tokenizer=nltk.word_tokenize,agg='mean'):
    if type(doc)==str:
        doc = tokenizer(doc)
    assert type(doc)==list,"please input either a list or a string"
    matches = []
    for w in doc:
        w = w.lower()
        if w in w2affects:
            matches.append(w2affects[w])
    if len(matches)==0:
        return {'anger':np.nan,'joy':np.nan,'sadness':np.nan,'fear':np.nan}
    scores = pd.DataFrame(matches)
    if agg=='mean':
        scores = scores.mean()
    elif agg=='max':
        scores =  scores.max()
    else:
        scores = agg(scores)
    return list(dict(scores).values())[0]


df_news ['Anger_intensity'] = df_news['cleaned_text_lemmas'].apply(lambda x: get_anger_score(x))


w2affect = []
for w,score,dim in AIL_df.values:
    w2affect.append({dim:score,'w':w})
w2affects = {}
df = pd.DataFrame(w2affect)
df = df.groupby('w').sum().reset_index()
for w,anger,fear,joy,sadness in df[['w','anger','fear','joy','sadness']].values:
    w2affects[w] = {'anger':anger,'joy':joy,'sadness':sadness,'fear':fear}

def get_joy_score(doc,tokenizer=nltk.word_tokenize,agg='mean'):
    if type(doc)==str:
        doc = tokenizer(doc)
    assert type(doc)==list,"please input either a list or a string"
    matches = []
    for w in doc:
        w = w.lower()
        if w in w2affects:
            matches.append(w2affects[w])
    if len(matches)==0:
        return {'anger':np.nan,'joy':np.nan,'sadness':np.nan,'fear':np.nan}
    scores = pd.DataFrame(matches)
    if agg=='mean':
        scores = scores.mean()
    elif agg=='max':
        scores =  scores.max()
    else:
        scores = agg(scores)
    return list(dict(scores).values())[1]

# get_joy_score('είσαι ωραίος και χαίρομαι να σε βλέπω.')
df_news ['Joy_intensity'] = df_news['cleaned_text_lemmas'].apply(lambda x: get_joy_score(x))


w2affect = []
for w,score,dim in AIL_df.values:
    w2affect.append({dim:score,'w':w})
w2affects = {}
df = pd.DataFrame(w2affect)
df = df.groupby('w').sum().reset_index()
for w,anger,fear,joy,sadness in df[['w','anger','fear','joy','sadness']].values:
    w2affects[w] = {'anger':anger,'joy':joy,'sadness':sadness,'fear':fear}

def get_sadness_score(doc,tokenizer=nltk.word_tokenize,agg='mean'):
    if type(doc)==str:
        doc = tokenizer(doc)
    assert type(doc)==list,"please input either a list or a string"
    matches = []
    for w in doc:
        w = w.lower()
        if w in w2affects:
            matches.append(w2affects[w])
    if len(matches)==0:
        return {'anger':np.nan,'joy':np.nan,'sadness':np.nan,'fear':np.nan}
    scores = pd.DataFrame(matches)
    if agg=='mean':
        scores = scores.mean()
    elif agg=='max':
        scores =  scores.max()
    else:
        scores = agg(scores)
    return list(dict(scores).values())[2]

df_news ['Sadness_intensity'] = df_news['cleaned_text_lemmas'].apply(lambda x: get_sadness_score(x))

w2affect = []
for w,score,dim in AIL_df.values:
    w2affect.append({dim:score,'w':w})
w2affects = {}
df = pd.DataFrame(w2affect)
df = df.groupby('w').sum().reset_index()
for w,anger,fear,joy,sadness in df[['w','anger','fear','joy','sadness']].values:
    w2affects[w] = {'anger':anger,'joy':joy,'sadness':sadness,'fear':fear}

def get_fear_score(doc,tokenizer=nltk.word_tokenize,agg='mean'):
    if type(doc)==str:
        doc = tokenizer(doc)
    assert type(doc)==list,"please input either a list or a string"
    matches = []
    for w in doc:
        w = w.lower()
        if w in w2affects:
            matches.append(w2affects[w])
    if len(matches)==0:
        return {'anger':np.nan,'joy':np.nan,'sadness':np.nan,'fear':np.nan}
    scores = pd.DataFrame(matches)
    if agg=='mean':
        scores = scores.mean()
    elif agg=='max':
        scores =  scores.max()
    else:
        scores = agg(scores)
    return list(dict(scores).values())[3]

df_news ['Fear_intensity'] = df_news['cleaned_text_lemmas'].apply(lambda x: get_fear_score(x))
df_news [['Anger_intensity','Joy_intensity', 'Sadness_intensity','Fear_intensity']]
cols = ['Anger_intensity', 'Joy_intensity', 'Sadness_intensity',
       'Fear_intensity']
df_news[cols] = df_news[cols].apply(pd.to_numeric, errors='coerce', axis=1)
df_news['joy'] =  df_news['joy']/ df_news['uniq_wds'] *100
df_news = df_news.rename(columns={' anticipation': 'anticipation','num_wds': 'length'})
df_news['anger'] =  df_news['anger']/ df_news['uniq_wds'] *100
df_news['disgust'] =  df_news['disgust']/ df_news['uniq_wds'] *100
df_news['fear'] =  df_news['fear']/ df_news['uniq_wds'] *100
df_news['surprise'] =  df_news['surprise']/ df_news['uniq_wds'] *100
df_news['trust'] =  df_news['trust']/ df_news['uniq_wds'] *100
df_news['sadness'] =  df_news['sadness']/ df_news['uniq_wds'] *100
df_news['negative'] =  df_news['negative']/ df_news['uniq_wds'] *100
df_news['positive'] =  df_news['positive']/ df_news['uniq_wds'] *100
df_news['anticipation'] =  df_news['anticipation']/ df_news['uniq_wds'] *100

# ## Count celebrities etc

def get_entities(text):
    doc = nlp(text)
    entity_info = [(ent.text) for ent in doc.ents if (ent.label_ == 'PERSON')]
    return entity_info

doc = nlp("Ήρθε η Αννα Βισση και ο Σκοιχάς και ο βατραχος και η μηχανη και η Microsoft και η Ελλάδα")
for ent in doc.ents:
    print(ent.text, ent.label_)

get_entities(doc)
df_news['names'] = df_news['article_text'].apply(lambda x: get_entities(x))
df_news['names'] = df_news['names'].apply(', '.join)

def drop_duplicates(row):
    # Split string by ', ', drop duplicates and join back.
    words = row.split(', ')
    return ', '.join(np.unique(words).tolist())

# drop_duplicates is applied to all rows of df.
df_news['names'] = df_news['names'].apply(drop_duplicates)
politicians = pd.read_csv('/home/iqjournalism/iq_journalism/TO_KEEP/politiciansel.csv')
politicians_list = politicians['Politician'].tolist()
celebrities = pd.read_csv('/home/iqjournalism/iq_journalism/TO_KEEP/celebrities')
[celebrities['celebrities'].unique() for col_name in celebrities.columns]
celebrities_list = celebrities['celebrities'].tolist()
celebrities_list
joinedlist = celebrities_list + politicians_list
def find_celebs(row):
    celebrities = []
    # Split string by ', ', drop duplicates and join back.
    words = row.split(', ')
    for i in words:
        if i in joinedlist:
            
            celebrities.append(i)
    return celebrities
                


# drop_duplicates is applied to all rows of df.
df_news['celebrities'] = df_news['names'].apply(find_celebs)

def count_celebs(row):
    celebrities = []
    count  =0
    # Split string by ', ', drop duplicates and join back.
    words = row.split(', ')
    for i in words:
        if i in joinedlist: 
            celebrities.append(i)
            count +=1 
    return count
                
# drop_duplicates is applied to all rows of df.
df_news['No Celebs'] = df_news['names'].apply(count_celebs)

# df_news['No Celebs'].value_counts()
animals = pd.read_csv('/home/iqjournalism/iq_journalism/TO_KEEP/animals')
animal_list = animals['ζώο'].tolist()

def find_animals(row):
    animals = []
    words = row.split(' ')
    for i in words:
        if i in animal_list:
            animals.append(i)
    return animals
                
df_news['animal'] = df_news['article_text'].apply(find_animals)
# df_news['animal'].value_counts().head(200)

def count_animals(row):
    animals = []
    count  =0
    words = row.split(' ')
    for i in words:
        
        if i in animal_list:
            
            animals.append(i)
            count +=1 
    return count

df_news['animal'] = df_news['article_text'].apply(count_animals)
crime = pd.read_csv('/home/iqjournalism/iq_journalism/TO_KEEP/crime.csv')

crime['crime'] = crime['crime'].str.lower()
crime_list = crime['crime'].tolist()

def find_crime(row):
    crime = []
    # Split string by ', ', drop duplicates and join back.
    words = row.split(' ')
    #print(words)
    for i in words:
        if i in crime_list:
            
            crime.append(i)
    return crime
                
# drop_duplicates is applied to all rows of df.
df_news['crime'] = df_news['article_text'].apply(find_crime)

def count_crime(row):
    crime = []
    count  =0
    words = row.split(' ')
    for i in words:
        if i in crime_list:
            crime.append(i)
            count +=1 
    return count
df_news['crime'] = df_news['article_text'].apply(count_crime)


sensual = pd.read_csv('/home/iqjournalism/iq_journalism/TO_KEEP/sensual.csv')
sensual_list = sensual['sensual'].tolist()
def count_sensual(row):
    sensual = []
    count  =0
    # Split string by ', ', drop duplicates and join back.
    words = row.split(' ')
    for i in words:
        
        if i in sensual_list:
            
            sensual.append(i)
            count +=1 
    return count
                


# drop_duplicates is applied to all rows of df.
df_news['sensual'] = df_news['article_text'].apply(count_sensual)


regex = r'\b[A-Z]{5,}\b'

df_news['caps_body'] = df_news['article_text'].str.findall(regex)
df_news['caps_body'] =df_news['caps_body'].str.len()

df_news['numbers'] = df_news['article_text'].apply(lambda x: len([x for x in x.split() if x.isdigit()]))



def find_links(text):
    urls = re.findall('https?://(?:[-\w.]|(?:%[\da-fA-F]{2}))+', text)
    return urls

def number_links(text):
    urls = re.findall('https?://(?:[-\w.]|(?:%[\da-fA-F]{2}))+', text)
    number = len(urls)
    return number

df_news['urls'] = df_news.article_text.apply(find_links)
# df_news['urls'].value_counts()
df_news['urls'] = df_news.article_text.apply(number_links)
df_news.to_csv(filename+'ks_runnable.csv', index = False)
