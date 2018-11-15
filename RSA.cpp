#include <stdio.h>
int exponentiation_mod(int a, int b, int c);
int gcd(int a, int b);
bool judge_e(int e, int t);


int main() {
	int p,q,e;   //�û����� 
	int n,o_n;    // t = n��ŷ������ 
	int d;      // d = �������˽Կ 
	int m;      // m = ����
	int c;      // c = ����
			    
	char s;
	printf("��������������p,q: ");
	scanf("%d %d",&p,&q);           // 1. ����p��q��������
	n = p*q;                        // 2. n = pq
	o_n = (p-1)*(q-1);                // 3. n��ŷ������(n)=(p-1)(q-1)
	printf("������һ�� e����������������(n)��Ϊ����: ");
	scanf("%d",&e);                 // 4. �����������e������e����n��ŷ����������,��1<e< (n) 
	while(!judge_e(e,o_n)){
		printf("e���Ϸ�������������: ");
		scanf("%d",&e);
	}
	
	d = 1;
	while(((e*d)%o_n) != 1) 
		d++;                      // 5. ����˽Կd
	bool flag = false;
	printf("������Ҫ���ܵ�����m:\n");
	scanf("%d",&m);
	c = exponentiation_mod(m,e,n);  // ͨ����ʽ C=Me mod n ������
	printf("m=%d���ܺ������c=%d\n",m,c);
	
	printf("������Ҫ���ܵ�����c:\n");
	scanf("%d",&c);
	m = exponentiation_mod(c,d,n);   // ͨ����ʽ������
	printf("c=%d���ܺ������m=%d\n",c,m); 
	
	return 0;
}

int exponentiation_mod(int a, int b, int c) {   // ģ������ 
	int r = 1;
	b = b+1;
	while( b!=1 ){
		r = r*a;
		r = r%c;
		b--;
	}
	return r;
}

int gcd(int a, int b) {   // �������������Լ�� 
	int tmp,c;
	if(a<b)	{
		tmp = a;
		a = b;
		b = tmp;
	}
	while ((a%b) != 0) {
		c = a%b;
		a = b;
		b = c;
	}
	return b;
}

bool judge_e(int e, int t) {  //�ж�e�Ƿ�Ϸ� 
	if(gcd(e,t) != 1) {
		return false;
	}
	if(e<1 || e>t) {
		return false;
	}
	return true;
}


