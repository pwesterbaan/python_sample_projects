class mergeable_priority_queue:
#--------------------Constructor----------------------------------
    class _Item:
        __slots__ = '_container', '_element', '_parent', '_leftChild', '_rightChild', '_distLabel'

        def __init__(self, container, e):
            """Create a new position that is the leader of its own group."""
            self._container  = container        # reference to mpq instance
            self._element    = e
            self._parent     = self
            self._leftChild  = None
            self._rightChild = None
            self._distLabel  = 1

        def __str__(self):
            return str(self._element)

        def __lt__(self,other):
            return self._element < other._element

        def _correct_distLabel(self):
            lc_dist=self._get_child_distance('leftChild')
            rc_dist=self._get_child_distance('rightChild')
            self.set_distLabel(min(lc_dist,rc_dist)+1)
            if self._parent != self:
                self._parent._correct_distLabel()

        def _get_child_distance(self,child='leftChild'):
            if 'leftChild' == child:
                return self._leftChild.get_distLabel() if self._leftChild != None else 0
            else:
                return self._rightChild.get_distLabel() if self._rightChild != None else 0

        def _check_leftist_heap(self):
            lc_dist=self._get_child_distance('leftChild')
            rc_dist=self._get_child_distance('rightChild')
            if lc_dist < rc_dist:
                self.swap_children()

        def meld(self,H2):
            """meld two mergeablePriorityQueue items
            restore heap order
            restore leftest property - update distLabels, set leftChild & rightChild
            return new melded heap"""
            if self == None:
                return H2
            elif H2 == None:
                return self
            elif self < H2:
                minheap, maxheap = self, H2
            else:
                minheap, maxheap = H2, self
            #get right branch of minheap
            rightBranch = minheap.get_rightChild()
            if rightBranch != None:
                rightBranch=rightBranch.meld(maxheap)
                #set right child and fix distance label
                if minheap != rightBranch:
                    minheap.set_rightChild(rightBranch)
            elif maxheap != None:
                #set right child and fix distance label
                if minheap != maxheap:
                    minheap.set_rightChild(maxheap)
            minheap._check_leftist_heap()
            return minheap

        #------ get methods ------#
        def get_element(self):
            return self._element

        def get_parent(self):
            return self._parent

        def get_leftChild(self):
            return self._leftChild

        def get_rightChild(self):
            return self._rightChild

        def get_distLabel(self):
            return self._distLabel

        #------ set methods ------#
        def set_parent(self,new_parent):
            self._parent=new_parent

        def set_leftChild(self, lc):
            self._leftChild=lc
            if lc != None:
                lc.set_parent(self)
            self._correct_distLabel()

        def set_rightChild(self, rc):
            self._rightChild=rc
            if rc != None:
                rc.set_parent(self)
            self._correct_distLabel()

        def set_distLabel(self, new_distLabel):
            self._distLabel=new_distLabel

        def swap_children(self):
            self._leftChild, self._rightChild = self._rightChild, self._leftChild

#----------------------Methods-------------------------------------

    def make_mpq(self,e):
        """Makes a new mpq containing element e, and returns its _Item."""
        return self._Item(self,e)

    def pop_min(self,mpq):
        """return root of mpq
        meld two subtrees from leftChild and rightChild"""
        rootNode=mpq
        lc=rootNode.get_leftChild()
        rc=rootNode.get_rightChild()
        if lc != None:
            lc.meld(rc)
            lc.set_parent(lc)
            mpq=lc
        elif rc != None:
            rc.set_parent(rc)
            mpq=rc
        else:
            mpq=None
        return rootNode._element, mpq
