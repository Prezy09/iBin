import pathlib 
lines=pathlib.Path('collection.php').read_text().splitlines() 
start=470; end=525 
for i in range(start,end): 
    print(f'{i+1:03}: {lines[i]}') 
