class Priority_Queue():
    
    class _Item:
        __slots__ = '_key','_value'

        def __init__(self, key, value):
            """initialize items in pq sorted by key associated with value"""
            self._key = key
            self._value = value

        def __lt__(self, other):
            """overload __lt__ to compare keys"""
            return self._key < other._key

    def __init__(self):
        """Initialize blank priority queue"""
        self._data = []

    def __len__(self):
        """Number of items in priority queue"""
        return len(self._data)

    def is_empty(self):
        return len(self) == 0
    
    def push(self, obj):
        # append at end and bubble up
        self._data.append(obj)
        n = len(self._data)
        self._bubble_up(n-1) 
        
    def pop(self):
        n = len(self._data)
        if n==0:
            return None
        if n==1:
            return self._data.pop()
        
        # replace with last item and sift down:
        obj = self._data[0]
        self._data[0] = self._data.pop()
        self._sift_down(0)
        return obj
    
    def _parent(self, n):
        return (n-1)//2

    def _left_child(self, n):
        return 2*n + 1

    def _right_child(self, n):
        return 2*n + 2

    def _bubble_up(self, index):
        while index>0:
            cur_item = self._data[index]
            parent_idx = self._parent(index)
            parent_item = self._data[parent_idx]
            
            if cur_item < parent_item:
                # swap with parent
                self._data[parent_idx] = cur_item
                self._data[index] = parent_item
                index = parent_idx
            else:
                break
    
    def _sift_down(self,index):
        n = len(self._data)
        
        while index<n:           
            cur_item = self._data[index]
            lc = self._left_child(index)
            if n <= lc:
                break

            # first set small child to left child:
            small_child_item = self._data[lc]
            small_child_idx = lc
            
            # right exists and is smaller?
            rc = self._right_child(index)
            if rc < n:
                r_item = self._data[rc]
                if r_item < small_child_item:
                    # right child is smaller than left child:
                    small_child_item = r_item
                    small_child_idx = rc
            
            # done: we are smaller than both children:
            if cur_item <= small_child_item:
                break
            
            # swap with smallest child:
            self._data[index] = small_child_item
            self._data[small_child_idx] = cur_item
            
            # continue with smallest child:
            index = small_child_idx
    
    def heapify(self, items):
        """ Take an array of unsorted items and replace the contents
        of this priority queue by them. """
        n=len(items)
        for i in range(n):
            self.push((items[i],i))

    def decrease_priority(self, old, new):
        # replace old by new and we can assume that new will compare smaller
        # (so priority is higher or the value is smaller)
        assert(new <= old)
        #The search
        n=len(self._data)
        for i in range(n):
            tmp=self._data[i]
            if tmp[0]==old:
                oldInd=i
                break
        #The swap
        self._data[oldInd]=(new,oldInd)
        #Start at the old node and bubble it up into place
        self._bubble_up(oldInd)